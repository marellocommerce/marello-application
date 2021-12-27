<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class PurchaseOrderOnOrderOnDemandCreationListener
{
    const ORDER_ON_DEMAND = 'order_on_demand';
    
    /**
     * @var int
     */
    private $orderId;
    
    /**
     * @var AvailableInventoryProvider
     */
    private $availableInventoryProvider;

    /** @var ConfigManager $configManager */
    private $configManager;

    /**
     * @param AvailableInventoryProvider $availableInventoryProvider
     */
    public function __construct(AvailableInventoryProvider $availableInventoryProvider)
    {
        $this->availableInventoryProvider = $availableInventoryProvider;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Order) {
            return;
        }

        if (!$this->configManager->get('marello_inventory.inventory_on_demand_enabled')
            || !$this->configManager->get('marello_inventory.inventory_on_demand')
        ) {
            return;
        }

        $orderOnDemandItems = [];
        $salesChannel = $entity->getSalesChannel();
        foreach ($entity->getItems() as $item) {
            if ($this->isOrderOnDemandItem($item, $salesChannel)) {
                $orderOnDemandItems[] = $item;
            }
        }
        if (!empty($orderOnDemandItems)) {
            $this->orderId = $entity->getId();
        }
    }
    
    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->orderId) {
            $entityManager = $args->getEntityManager();
            /** @var Order $entity */
            $entity = $entityManager
                ->getRepository(Order::class)
                ->find($this->orderId);
            if ($entity) {
                $this->orderId = null;
                $warehouse = $this->getLinkedWarehouse($entity, $entityManager);
                if (!$warehouse) {
                    return;
                }
                $orderOnDemandItems = [];
                $salesChannel = $entity->getSalesChannel();
                foreach ($entity->getItems() as $item) {
                    if ($this->isOrderOnDemandItem($item, $salesChannel)) {
                        $orderOnDemandItems[] = $item;
                    }
                }
                $poBySuppliers = [];
                $itemsBySuppliers = [];
                $organization = $entity->getOrganization();
                /** @var OrderItem $onDemandItem */
                foreach ($orderOnDemandItems as $onDemandItem) {
                    $product = $onDemandItem->getProduct();
                    $supplier = $product->getPreferredSupplier();
                    $supplierName = $supplier->getName();
                    $itemsBySuppliers[$supplierName][] = $onDemandItem->getId();
                    if (!isset($poBySuppliers[$supplierName])) {
                        $po = new PurchaseOrder();
                        $po
                            ->setSupplier($supplier)
                            ->setOrganization($organization);
                        $poBySuppliers[$supplierName] = $po;
                    }
                    /** @var PurchaseOrder $po */
                    $po = $poBySuppliers[$supplierName];
                    $qty = $onDemandItem->getQuantity();
                    $price = $this->getPurchasePrice($product);
                    $poItem = new PurchaseOrderItem();
                    $poItem
                        ->setProduct($product)
                        ->setOrderedAmount($onDemandItem->getQuantity())
                        ->setRowTotal($price->getValue() * $qty)
                        ->setPurchasePrice($price);
                    $po->addItem($poItem);
                }
                foreach ($poBySuppliers as $po) {
                    $orderTotal = 0.00;
                    foreach ($po->getItems() as $poi) {
                        $orderTotal += $poi->getRowTotal();
                    }
                    $po
                        ->setOrderTotal($orderTotal)
                        ->setWarehouse($warehouse)
                        ->setCreatedAt(new \DateTime())
                        ->setData([self::ORDER_ON_DEMAND => [
                            'order' => $entity->getId(),
                            'orderItems' => $itemsBySuppliers[$po->getSupplier()->getName()]
                        ]
                        ]);
                    $entityManager->persist($po);
                }
                $entityManager->flush();
            }
        }
    }
    
    /**
     * @param Product $product
     * @return ProductPrice|null
     */
    private function getPurchasePrice(Product $product)
    {
        $supplier = $product->getPreferredSupplier();
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if ($productSupplierRelation->getSupplier() === $supplier) {
                $productPrice = new ProductPrice();
                $productPrice
                    ->setValue($productSupplierRelation->getCost())
                    ->setProduct($product)
                    ->setCurrency($supplier->getCurrency());

                return $productPrice;
            }
        }

        return null;
    }

    /**
     * @param OrderItem $orderItem
     * @param SalesChannel $salesChannel
     * @return bool
     */
    private function isOrderOnDemandItem(OrderItem $orderItem, SalesChannel $salesChannel)
    {
        $product = $orderItem->getProduct();
        $orderedQty = $orderItem->getQuantity();
        $result = $this->availableInventoryProvider->getAvailableInventory($product, $salesChannel);
        if ($orderedQty > $result && $this->isOrderOnDemandAllowed($product)) {
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isOrderOnDemandAllowed(Product $product)
    {
        foreach ($product->getInventoryItems() as $inventoryItem) {
            if ($inventoryItem->isOrderOnDemandAllowed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Order $order
     * @param EntityManager $manager
     * @return Warehouse|null
     */
    private function getLinkedWarehouse(Order $order, EntityManager $manager)
    {
        /** @var WarehouseChannelGroupLink $warehouseGroupLink */
        $warehouseGroupLink = $manager->getRepository(WarehouseChannelGroupLink::class)
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());

        if (!$warehouseGroupLink) {
            return null;
        }

        /** @var Warehouse[] $linkedWarehouses */
        $linkedWarehouses = $warehouseGroupLink
            ->getWarehouseGroup()
            ->getWarehouses()
            ->toArray();

        return !empty($linkedWarehouses) ? reset($linkedWarehouses) : null;
    }

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }
}
