<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;

class PurchaseOrderOnOrderOnDemandCreationListener
{
    const ORDER_ON_DEMAND = 'order_on_demand';
    
    /**
     * @var int
     */
    private $allocationId;

    public function __construct(
        private ConfigManager $configManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Allocation) {
            return;
        }

        if (!$this->configManager->get('marello_order.order_on_demand_enabled')
            || !$this->configManager->get('marello_order.order_on_demand')
        ) {
            return;
        }

        if ($entity->getAllocationContext()
            && $entity->getAllocationContext()->getId() === AllocationContextInterface::ALLOCATION_CONTEXT_REALLOCATION
        ) {
            return;
        }
        $orderOnDemandItems = [];
        foreach ($entity->getItems() as $item) {
            $inventoryItem = $item->getOrderItem()->getInventoryItem();
            if ($inventoryItem->isEnableBatchInventory()
                && $this->isOrderOnDemandItem($item->getProduct())
                && $entity->getState()->getId() === AllocationStateStatusInterface::ALLOCATION_STATE_WFS
            ) {
                $orderOnDemandItems[] = $item;
            }
        }
        if (!empty($orderOnDemandItems)) {
            $this->allocationId = $entity->getId();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->allocationId) {
            return;
        }

        $entityManager = $args->getObjectManager();
        /** @var Allocation $allocation */
        $allocation = $entityManager
            ->getRepository(Allocation::class)
            ->find($this->allocationId);
        $this->allocationId = null;
        if (!$allocation) {
            return;
        }

        [$poBySupplier, $allocationItemsBySupplier] = $this->createPurchaseOrdersFromAllocation(
            $allocation,
            $entityManager
        );
        $this->updatePurchaseOrdersTotal($poBySupplier, $allocationItemsBySupplier, $allocation);
        $entityManager->flush();
    }

    private function createPurchaseOrdersFromAllocation(
        Allocation $allocation,
        EntityManager $entityManager
    ): array {
        $poBySupplier = [];
        $allocationItemsBySupplier = [];

        $warehouse = $this->getOnDemandLocation($allocation, $entityManager);
        $organization = $allocation->getOrganization();
        if (!$warehouse) {
            /** @var NotificationMessageContext $context */
            $context = $this->createNotificationContext(
                $organization,
                'marello.notificationmessage.purchaseorder.no_ood_warehouse_configured.title',
                'marello.notificationmessage.purchaseorder.no_ood_warehouse_configured.message',
                'marello.notificationmessage.purchaseorder.no_ood_warehouse_configured.solution',
            );
            $this->eventDispatcher
                ->dispatch(
                    new CreateNotificationMessageEvent($context),
                    CreateNotificationMessageEvent::NAME
                );
            throw new \LogicException('To create Purchase Order you need to specify an On Demand Location warehouse');
        }

        foreach ($allocation->getItems() as $allocationItem) {
            if (!$this->isOrderOnDemandItem($allocationItem->getProduct())) {
                continue;
            }

            $product = $allocationItem->getProduct();
            $supplier = $product->getPreferredSupplier();
            $supplierCode = $supplier->getCode();
            $allocationItemsBySupplier[$supplierCode][] = $allocationItem->getId();

            // Create Purchase Order if not exist
            if (!isset($poBySupplier[$supplierCode])) {
                $po = new PurchaseOrder();
                $po
                    ->setSupplier($supplier)
                    ->setOrganization($organization)
                    ->setWarehouse($warehouse);

                $entityManager->persist($po);
                $poBySupplier[$supplierCode] = $po;
            }

            /** @var PurchaseOrder $po */
            $po = $poBySupplier[$supplierCode];
            $price = $this->getPurchasePrice($product, $allocation->getOrder());
            $poItem = new PurchaseOrderItem();
            $poItem
                ->setProduct($product)
                ->setOrderedAmount($allocationItem->getQuantity())
                ->setRowTotal($price->getValue() * $allocationItem->getQuantity())
                ->setPurchasePrice($price)
                ->setData([
                    self::ORDER_ON_DEMAND =>
                        [
                            'order' => $allocation->getOrder()->getId(),
                            'orderItem' => $allocationItem->getOrderItem()->getId(),
                            'allocation' => $allocation->getId(),
                            'allocationItem' => $allocationItem->getId()
                        ]
                ]);

            $po->addItem($poItem);
            $entityManager->persist($poItem);

            $this->createInventoryBatch($allocationItem, $warehouse, $entityManager);
        }

        return [$poBySupplier, $allocationItemsBySupplier];
    }

    /**
     * @param array|PurchaseOrder[] $poBySupplier
     * @param array $allocationItemsBySupplier
     * @param Allocation $allocation
     * @return void
     */
    private function updatePurchaseOrdersTotal(
        array $poBySupplier,
        array $allocationItemsBySupplier,
        Allocation $allocation
    ): void
    {
        foreach ($poBySupplier as $po) {
            $orderTotal = 0.00;
            foreach ($po->getItems() as $poi) {
                $orderTotal += $poi->getRowTotal();
            }

            $po
                ->setOrderTotal($orderTotal)
                ->setData(
                    [
                        self::ORDER_ON_DEMAND =>
                            [
                                'order' => $allocation->getOrder()->getId(),
                                'allocation' => $allocation->getId(),
                                'allocationItems' => $allocationItemsBySupplier[$po->getSupplier()->getCode()]
                            ]
                    ]
                );
        }
    }

    /**
     * @param Product $product
     * @param Order $order
     * @return ProductPrice|null
     */
    private function getPurchasePrice(Product $product, Order $order)
    {
        $supplier = $product->getPreferredSupplier();
        $productPrice = new ProductPrice();
        $productPrice
            ->setValue(0)
            ->setProduct($product)
            ->setCurrency($order->getCurrency());
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if ($productSupplierRelation->getSupplier() === $supplier) {
                $productPrice
                    ->setValue($productSupplierRelation->getCost())
                    ->setProduct($product)
                    ->setCurrency($supplier->getCurrency());
                return $productPrice;
            }
        }

        return $productPrice;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isOrderOnDemandItem(Product $product)
    {
        $inventoryItem = $product->getInventoryItem();
        if ($inventoryItem && $inventoryItem->isOrderOnDemandAllowed()) {
            return true;
        }

        return false;
    }

    private function createInventoryBatch(
        AllocationItem $allocationItem,
        Warehouse $warehouse,
        EntityManager $entityManager
    ): void {
        $allocation = $allocationItem->getAllocation();

        $inventoryLevels = $allocationItem->getProduct()->getInventoryItem()->getInventoryLevels();
        if ($inventoryLevels->count() === 0) {
            $context = $this->createNotificationContext(
                $warehouse->getOwner(),
                'marello.notificationmessage.purchaseorder.no_inventory_levels.title',
                'marello.notificationmessage.purchaseorder.no_inventory_levels.message',
                'marello.notificationmessage.purchaseorder.no_inventory_levels.solution',
            );
            $this->eventDispatcher
                ->dispatch(
                    new CreateNotificationMessageEvent($context),
                    CreateNotificationMessageEvent::NAME
                );

            throw new \LogicException(
                sprintf('No inventory levels found for Product sku: %s', $allocationItem->getProductSku())
            );
        }
        foreach ($inventoryLevels as $inventoryLevel) {
            if ($inventoryLevel->getWarehouse() !== $warehouse) {
                continue;
            }
            $inventoryBatch = InventoryBatchFromInventoryLevelFactory::createInventoryBatch($inventoryLevel);
            $inventoryBatch->setOrganization($allocation->getOrganization());
            $inventoryBatch->setOrderOnDemandRef($allocationItem->getOrderItem()->getId());
            $inventoryBatch->setQuantity(0);

            $entityManager->persist($inventoryBatch);
        }
    }

    private function getOnDemandLocation(Allocation $allocation, EntityManager $entityManager): ?Warehouse
    {
        /** @var WarehouseChannelGroupLink $channelGroupLink */
        $channelGroupLink = $entityManager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findLinkBySalesChannelGroup($allocation->getOrder()->getSalesChannel()->getGroup());
        $onDemandLocations = $entityManager->getRepository(Warehouse::class)->findBy(
            [
                'orderOnDemandLocation' => true,
                'group' => $channelGroupLink->getWarehouseGroup(),
            ],
            ['sortOrderOodLoc' => 'ASC'],
            1
        );

        return $onDemandLocations[0] ?? null;
    }

    /**
     * @param OrganizationInterface $organization
     * @param $title
     * @param $message
     * @param $solution
     * @return NotificationMessageContext
     */
    protected function createNotificationContext(
        OrganizationInterface $organization,
        $title,
        $message,
        $solution
    ) {
        return NotificationMessageContextFactory::create(
            NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_ERROR,
            NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_PURCHASE_ORDER,
            $title,
            $message,
            $solution,
            null,
            'allocating',
            null,
            null,
            null,
            $organization,
            false,
            true
        );
    }
}
