<?php

namespace Marello\Bundle\OrderBundle\EventListener\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Event\OrderItemsForNotificationEvent;
use Marello\Bundle\OrderBundle\Event\OrderShippingContextBuildingEvent;
use Marello\Bundle\OrderBundle\Factory\OrderShippingContextFactory;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\PackingBundle\Event\AfterPackingSlipCreationEvent;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine\PurchaseOrderOnOrderOnDemandCreationListener;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PurchaseOrderWorkflowCompletedListener
{
    /**
     * @var array
     */
    private $removedItems = [];

    /**
     * @var OrderItem[]
     */
    private $onDemandItems = [];

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var AvailableInventoryProvider
     */
    private $availableInventoryProvider;

    /**
     * @var OrderShippingContextFactory
     */
    private $orderShippingContextFactory;

    /**
     * @var ShippingMethodProviderInterface
     */
    private $shippingMethodProvider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ServiceLink
     */
    protected $emailSendProcessorLink;

    /**
     * @var PackingSlip|null
     */
    private $packingSlip;

    /**
     * @param Registry $doctrine
     * @param WorkflowManager $workflowManager
     * @param AvailableInventoryProvider $availableInventoryProvider
     * @param OrderShippingContextFactory $orderShippingContextFactory
     * @param ShippingMethodProviderInterface $shippingMethodProvider
     * @param EventDispatcherInterface $eventDispatcher
     * @param ServiceLink $emailSendProcessorLink
     */
    public function __construct(
        Registry $doctrine,
        WorkflowManager $workflowManager,
        AvailableInventoryProvider $availableInventoryProvider,
        OrderShippingContextFactory $orderShippingContextFactory,
        ShippingMethodProviderInterface $shippingMethodProvider,
        EventDispatcherInterface $eventDispatcher,
        ServiceLink $emailSendProcessorLink
    ) {
        $this->doctrine = $doctrine;
        $this->workflowManager = $workflowManager;
        $this->availableInventoryProvider = $availableInventoryProvider;
        $this->orderShippingContextFactory = $orderShippingContextFactory;
        $this->shippingMethodProvider = $shippingMethodProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->emailSendProcessorLink = $emailSendProcessorLink;
    }

    /**
     * @param InventoryUpdateEvent $event
     * @throws \Exception
     */
    public function onPurchaseOrderCompleted(InventoryUpdateEvent $event)
    {
        $context = $event->getInventoryUpdateContext();
        if (!$context->getRelatedEntity() instanceof PurchaseOrder) {
            return;
        }

        /** @var PurchaseOrder $entity */
        $entity = $context->getRelatedEntity();
        $data = $entity->getData();
        $orderOnDemandKey = PurchaseOrderOnOrderOnDemandCreationListener::ORDER_ON_DEMAND;
        if (isset($data[$orderOnDemandKey])) {
            /** @var Order $order */
            $order = $this->doctrine
                ->getManagerForClass(Order::class)
                ->getRepository(Order::class)
                ->find($data[$orderOnDemandKey]['order']);
            if ($order) {
                $hasShippedItems = false;
                $hasPackedItems = false;
                $entityManager = $this->doctrine->getManagerForClass(OrderItem::class);
                foreach ($order->getItems() as $item) {
                    $status = $item->getStatus()->getId();
                    if (!in_array($item->getId(), $data[$orderOnDemandKey]['orderItems'])) {
                        $statuses = [LoadOrderItemStatusData::DROPSHIPPING, LoadOrderItemStatusData::SHIPPED];
                        if (in_array($status, $statuses, true)) {
                            $hasShippedItems = true;
                        }
                    } elseif ($status === LoadOrderItemStatusData::WAITING_FOR_SUPPLY) {
                        $item->setStatus($this->findStatusByName(LoadOrderItemStatusData::PROCESSING));
                        $entityManager->persist($item);
                    }
                    $packingSlipItem = $this->doctrine
                        ->getManagerForClass(PackingSlipItem::class)
                        ->getRepository(PackingSlipItem::class)
                        ->findOneBy(['orderItem' => $item->getId()]);
                    if ($packingSlipItem) {
                        $hasPackedItems = true;
                    }
                    if ($hasPackedItems || $hasShippedItems) {
                        break;
                    }
                }
                $entityManager->flush();
                if ($hasPackedItems || $hasShippedItems) {
                    foreach ($order->getItems() as $item) {
                        if (!in_array($item->getId(), $data[$orderOnDemandKey]['orderItems'])) {
                            $this->removedItems[] = [
                                'sku' => $item->getProductSku(),
                                'quantity' => $item->getQuantity()
                            ];
                        } else {
                            $this->onDemandItems[] = $item;
                        }
                    }
                    $shippingContextArray = $this->orderShippingContextFactory->create($order);
                    $method = $order->getShippingMethod();
                    $methodType = $order->getShippingMethodType();
                    if ($shippingMethod = $this->shippingMethodProvider->getShippingMethod($method)) {
                        if ($shippingMethodType = $shippingMethod->getType($methodType)) {
                            $shipmentManager = $this->doctrine->getManagerForClass(Shipment::class);
                            foreach ($shippingContextArray as $shippingContext) {
                                $shipment = $shippingMethodType->createShipment($shippingContext, $method, $methodType);
                                if ($shipment) {
                                    $shipmentManager->persist($shipment);
                                }
                            }
                            $shipmentManager->flush();
                        }
                    }
                    $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($order);
                    foreach ($workflowItems as $workflowItem) {
                        $this->eventDispatcher->dispatch(
                            new ExtendableActionEvent($workflowItem),
                            'extendable_action.create_packingslip'
                        );
                    }
                    if ($this->packingSlip) {
                        $warehouse = $this->packingSlip->getWarehouse();
                        foreach ($this->packingSlip->getItems() as $packingSlipItem) {
                            $item = $packingSlipItem->getOrderItem();
                            $this->handleInventoryUpdate(
                                $item,
                                null,
                                $item->getQuantity(),
                                $order,
                                $warehouse,
                                'order_workflow.inventory_allocated',
                                false
                            );
                        }
                    }
                    $this->emailSendProcessorLink->getService()->sendNotification(
                        'marello_order_shipping_prepared',
                        [$order->getCustomer()],
                        $order
                    );
                }
                if ($hasShippedItems) {
                    if ($this->packingSlip) {
                        $entityManager = $this->doctrine->getManagerForClass(OrderItem::class);
                        $warehouse = $this->packingSlip->getWarehouse();
                        foreach ($this->packingSlip->getItems() as $packingSlipItem) {
                            $item = $packingSlipItem->getOrderItem();
                            $this->handleInventoryUpdate(
                                $item,
                                -$item->getQuantity(),
                                -$item->getQuantity(),
                                $order,
                                $warehouse,
                                'order_workflow.shipped',
                                true
                            );
                            $item->setStatus($this->findStatusByName(LoadOrderItemStatusData::SHIPPED));
                            $entityManager->persist($item);
                        }
                        $entityManager->flush();
                    }
                    $this->emailSendProcessorLink->getService()->sendNotification(
                        'marello_order_shipped_confirmation',
                        [$order->getCustomer()],
                        $order
                    );
                }
            }
        }
    }

    /**
     * @param OrderItemsForNotificationEvent $event
     */
    public function onSelectingOrderItemsForNotification(OrderItemsForNotificationEvent $event)
    {
        if ($this->removedItems) {
            $lineItems = $event->getOrderItems();
            foreach ($lineItems as $key => $lineItem) {
                $sku = $lineItem->getProductSku();
                $qty = $lineItem->getQuantity();
                foreach ($this->removedItems as $removedItem) {
                    if ($sku === $removedItem['sku'] && $qty === $removedItem['quantity']) {
                        unset($lineItems[$key]);
                    }
                }
            }
            if (empty($lineItems) && !empty($this->onDemandItems)) {
                $lineItems = $this->onDemandItems;
            }
            $event->setOrderItems($lineItems);
        }
    }

    /**
     * @param OrderShippingContextBuildingEvent $event
     */
    public function onOrderShippingContextBuilding(OrderShippingContextBuildingEvent $event)
    {
        $context = $event->getShippingContext();
        $order = $context->getSourceEntity();
        if (!$order instanceof Order || !$this->removedItems) {
            return;
        }
        /** @var ShippingLineItemInterface $lineItem */
        $lineItems = $context->getLineItems();
        $subtotal = 0.00;
        foreach ($lineItems as $lineItem) {
            $sku = $lineItem->getProductSku();
            $qty = $lineItem->getQuantity();
            foreach ($this->removedItems as $removedItem) {
                if ($sku === $removedItem['sku'] && $qty === $removedItem['quantity']) {
                    $lineItems->removeElement($lineItem);
                } else {
                    $subtotal += $lineItem->getPrice()->getValue() * $lineItem->getQuantity();
                }
            }
        }
        $subtotal = Price::create(
            $subtotal,
            $order->getCurrency()
        );
        $context->setSubtotal($subtotal);
    }
    
    /**
     * @param AfterPackingSlipCreationEvent $event
     */
    public function afterPackingSlipCreation(AfterPackingSlipCreationEvent $event)
    {
        if ($this->removedItems) {
            $packingSlip = $event->getPackingSlip();
            foreach ($packingSlip->getItems() as $slipItem) {
                $orderItem = $slipItem->getOrderItem();
                $sku = $orderItem->getProductSku();
                $qty = $orderItem->getQuantity();
                foreach ($this->removedItems as $removedItem) {
                    if ($sku === $removedItem['sku'] && $qty === $removedItem['quantity']) {
                        $packingSlip->removeItem($slipItem);
                    }
                }
            }
            if ($packingSlip->getItems()->count() === 0) {
                $event->setPackingSlip(null);
            } else {
                $this->packingSlip = $packingSlip;
            }
        }
    }

    /**
     * handle the inventory update for items which have been picked and packed
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Order $entity
     * @param Warehouse $warehouse
     * @param string $trigger
     * @param bool $isVirtual
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $entity,
        $warehouse,
        $trigger,
        $isVirtual
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            $trigger,
            $entity
        );

        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
        if ($isVirtual) {
            $context->setIsVirtual(true);
            $this->eventDispatcher->dispatch(
                new InventoryUpdateEvent($context),
                InventoryUpdateEvent::NAME
            );
        }
    }

    /**
     * @param string $name
     * @return null|object
     */
    private function findStatusByName($name)
    {
        $statusClass = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);
        $status = $this->doctrine
            ->getManagerForClass($statusClass)
            ->getRepository($statusClass)
            ->find($name);

        if ($status) {
            return $status;
        }

        return null;
    }
}
