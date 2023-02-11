<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderOnOrderOnDemandCreationListener
{
    const ORDER_ON_DEMAND = 'order_on_demand';
    
    /**
     * @var int
     */
    private $allocationId;

    /**
     * @var ConfigManager $configManager
     */
    private $configManager;

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
        if (!$this->allocationId) {
            return;
        }

        $entityManager = $args->getEntityManager();
        /** @var Allocation $allocation */
        $allocation = $entityManager
            ->getRepository(Allocation::class)
            ->find($this->allocationId);
        $this->allocationId = null;
        if (!$allocation) {
            return;
        }

        [$poBySupplier, $itemsBySupplier] = $this->createPurchaseOrdersFromAllocation(
            $allocation,
            $entityManager
        );
        $this->updatePurchaseOrdersTotal($poBySupplier, $itemsBySupplier, $allocation->getOrder());
        $entityManager->flush();

        $this->createTemporaryWarehouses($poBySupplier, $entityManager);
        $entityManager->flush();
    }

    private function createPurchaseOrdersFromAllocation(
        Allocation $allocation,
        EntityManager $entityManager
    ): array {
        $poBySupplier = [];
        $itemsBySupplier = [];

        $organization = $allocation->getOrganization();
        foreach ($allocation->getItems() as $onDemandItem) {
            if (!$this->isOrderOnDemandItem($onDemandItem->getProduct())) {
                continue;
            }

            $product = $onDemandItem->getProduct();
            $supplier = $product->getPreferredSupplier();
            $supplierCode = $supplier->getCode();
            $itemsBySupplier[$supplierCode][] = $onDemandItem->getId();
            if (!isset($poBySupplier[$supplierCode])) {
                $po = new PurchaseOrder();
                $po
                    ->setSupplier($supplier)
                    ->setOrganization($organization);

                $entityManager->persist($po);
                $poBySupplier[$supplierCode] = $po;
            }

            /** @var PurchaseOrder $po */
            $po = $poBySupplier[$supplierCode];
            $qty = $onDemandItem->getQuantity();
            $price = $this->getPurchasePrice($product, $allocation->getOrder());
            $poItem = new PurchaseOrderItem();
            $poItem
                ->setProduct($product)
                ->setOrderedAmount($onDemandItem->getQuantity())
                ->setRowTotal($price->getValue() * $qty)
                ->setPurchasePrice($price);
            $poItem->setData([
                self::ORDER_ON_DEMAND =>
                    [
                        'order' => $allocation->getOrder()->getId(),
                        'orderItem' => $onDemandItem->getId(),
                    ]
            ]);
            $po->addItem($poItem);
            $entityManager->persist($poItem);
        }

        return [$poBySupplier, $itemsBySupplier];
    }

    private function updatePurchaseOrdersTotal(array $poBySupplier, array $itemsBySupplier, Order $order): void
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
                                'order' => $order->getId(),
                                'orderItems' => $itemsBySupplier[$po->getSupplier()->getCode()]
                            ]
                    ]
                );
        }
    }

    private function createTemporaryWarehouses(array $poBySupplier, EntityManager $entityManager): void
    {
        $warehouseType = $entityManager
            ->getRepository(WarehouseType::class)
            ->findOneBy(['name' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL]);

        /** @var PurchaseOrder $po */
        foreach ($poBySupplier as $po) {
            $code = $po->getTemporaryWarehouseCode();
            $warehouse = new Warehouse();
            $warehouse->setWarehouseType($warehouseType);
            $warehouse->setDefault(false);
            $warehouse->setOwner($po->getOrganization());
            $warehouse->setCode($code);
            $warehouse->setLabel($code);
            $po->setWarehouse($warehouse);
            $entityManager->persist($warehouse);

            foreach ($po->getItems() as $poItem) {
                $inventoryItem = $poItem->getProduct()->getInventoryItems()->first();
                $inventoryLevel = new InventoryLevel();
                $inventoryLevel->setInventoryItem($inventoryItem);
                $inventoryLevel->setWarehouse($warehouse);
                $inventoryLevel->setOrganization($po->getOrganization());
                $entityManager->persist($inventoryLevel);
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
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }
}
