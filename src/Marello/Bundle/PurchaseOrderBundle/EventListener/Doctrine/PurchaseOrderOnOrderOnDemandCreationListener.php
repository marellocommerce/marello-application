<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class PurchaseOrderOnOrderOnDemandCreationListener
{
    const ORDER_ON_DEMAND = 'order_on_demand';
    
    /**
     * @var int
     */
    private $allocationId;

    /** @var ConfigManager $configManager */
    private $configManager;

    public function __construct(
        private AclHelper $aclHelper
    ) {}

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Allocation) {
            return;
        }

        if (!$this->configManager->get('marello_inventory.inventory_on_demand_enabled')
            || !$this->configManager->get('marello_inventory.inventory_on_demand')
        ) {
            return;
        }

        $orderOnDemandItems = [];
        foreach ($entity->getItems() as $item) {
            if ($this->isOrderOnDemandItem($item->getProduct()) &&
                $entity->getState()->getId() === AllocationStateStatusInterface::ALLOCATION_STATE_WFS
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
        if ($this->allocationId) {
            $entityManager = $args->getEntityManager();
            /** @var Allocation $entity */
            $entity = $entityManager
                ->getRepository(Allocation::class)
                ->find($this->allocationId);
            if ($entity) {
                $this->allocationId = null;
                $warehouse = $this->getLinkedWarehouse($entity->getOrder(), $entityManager);
                if (!$warehouse) {
                    return;
                }
                $orderOnDemandItems = [];
                foreach ($entity->getItems() as $item) {
                    if ($this->isOrderOnDemandItem($item->getProduct())) {
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
                    $supplierCode = $supplier->getCode();
                    $itemsBySuppliers[$supplierCode][] = $onDemandItem->getId();
                    if (!isset($poBySuppliers[$supplierCode])) {
                        $po = new PurchaseOrder();
                        $po
                            ->setSupplier($supplier)
                            ->setOrganization($organization);
                        $poBySuppliers[$supplierCode] = $po;
                    }
                    /** @var PurchaseOrder $po */
                    $po = $poBySuppliers[$supplierCode];
                    $qty = $onDemandItem->getQuantity();
                    $price = $this->getPurchasePrice($product, $entity->getOrder());
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
                        ->setData(
                            [
                                self::ORDER_ON_DEMAND =>
                                    [
                                        'order' => $entity->getOrder()->getId(),
                                        'orderItems' => $itemsBySuppliers[$po->getSupplier()->getCode()]
                                    ]
                            ]
                        );
                    $entityManager->persist($po);
                }
                $entityManager->flush();
            }
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
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup(), $this->aclHelper);

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
