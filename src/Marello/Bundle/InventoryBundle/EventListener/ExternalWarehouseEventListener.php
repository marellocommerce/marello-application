<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Model\Allocation\Notifier\WarehouseManualNotifier;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Event\ProductDropshipEvent;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Event\SupplierDropshipEvent;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ExternalWarehouseEventListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var Warehouse
     */
    protected $warehouse;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param SupplierDropshipEvent $event
     */
    public function onSupplierDropshipToggle(SupplierDropshipEvent $event)
    {
        $supplier = $event->getSupplier();
        if ($event->isCanDropship() === true) {
            $this->onSupplierDropshipOn($supplier);
        } else {
            $this->onSupplierDropshipOff($supplier);
        }
    }

    /**
     * @param ProductDropshipEvent $event
     */
    public function onProductDropshipToggle(ProductDropshipEvent $event)
    {
        $productSupplierRelation = $event->getProductSupplierRelation();
        if ($event->isCanDropship() === true) {
            $this->onProductDropshipOn($productSupplierRelation);
        } else {
            $this->onProductDropshipOff($productSupplierRelation);
        }
    }

    /**
     * @param Supplier $supplier
     */
    private function onSupplierDropshipOn(Supplier $supplier)
    {
        $warehouse = $this->getWarehouse($supplier);
        if (!$warehouse) {
            $warehouse = $this->createWarehouse($supplier);
        }
        $this->createInventoryLevelsForRelatedProducts($supplier, $warehouse);
    }

    /**
     * @param Supplier $supplier
     */
    private function onSupplierDropshipOff(Supplier $supplier)
    {
        $warehouse = $this->getWarehouse($supplier);
        if ($warehouse) {
            $this->removeInventoryLevelsForRelatedProducts($warehouse);
            $this->removeWarehouse($supplier);
        }
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     */
    private function onProductDropshipOn(ProductSupplierRelation $productSupplierRelation)
    {
        $warehouse = $this->getWarehouse($productSupplierRelation->getSupplier());
        if ($warehouse) {
            $entityManager = $this->doctrineHelper
                ->getEntityManagerForClass(InventoryLevel::class);
            $this->createInventoryLevelForRelatedProduct($productSupplierRelation, $warehouse, $entityManager);
        }
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     */
    private function onProductDropshipOff(ProductSupplierRelation $productSupplierRelation)
    {
        $warehouse = $this->getWarehouse($productSupplierRelation->getSupplier());
        if ($warehouse) {
            $entityManager = $this->doctrineHelper
                ->getEntityManagerForClass(InventoryLevel::class);
            $this->removeInventoryLevelForRelatedProduct($productSupplierRelation, $warehouse, $entityManager);
        }
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function getWarehouse(Supplier $supplier)
    {
        if ($this->warehouse === null) {
            $warehouseType = $this->doctrineHelper
                ->getEntityManagerForClass(WarehouseType::class)
                ->getRepository(WarehouseType::class)
                ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
            $warehouse = $this->doctrineHelper
                ->getEntityManagerForClass(Warehouse::class)
                ->getRepository(Warehouse::class)
                ->findOneBy([
                    'code' => $supplier->getCode(),
                    'warehouseType' => $warehouseType
                ]);
            if ($warehouse) {
                $this->warehouse = $warehouse;
            }
        }

        return $this->warehouse;
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function createWarehouse(Supplier $supplier)
    {
        $systemWarehouseGroup = $this->doctrineHelper
            ->getEntityManagerForClass(WarehouseGroup::class)
            ->getRepository(WarehouseGroup::class)
            ->findOneBy(['system' => true]);
        $warehouseType = $this->doctrineHelper
            ->getEntityManagerForClass(WarehouseType::class)
            ->getRepository(WarehouseType::class)
            ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);

        $warehouse = new Warehouse(sprintf('%s External Warehouse', $supplier->getName()));
        $warehouse
            ->setAddress(clone $supplier->getAddress())
            ->setCode($supplier->getCode())
            ->setWarehouseType($warehouseType)
            ->setNotifier(WarehouseManualNotifier::IDENTIFIER);
        if ($organization = $supplier->getOrganization()) {
            $warehouse->setOwner($organization);
        } else {
            $warehouse->setOwner($this->getOrganization());
        }
        if ($systemWarehouseGroup) {
            $warehouse->setGroup($systemWarehouseGroup);
        }

        $entityManager = $this->doctrineHelper
            ->getEntityManagerForClass(Warehouse::class);
        $entityManager->persist($warehouse);
        $entityManager->flush($warehouse);

        return $warehouse;
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function removeWarehouse(Supplier $supplier)
    {
        $warehouse = $this->getWarehouse($supplier);
        if ($warehouse) {
            $entityManager = $this->doctrineHelper
                ->getEntityManagerForClass(Warehouse::class);
            $entityManager->remove($warehouse);
            $entityManager->flush($warehouse);
        }
    }

    /**
     * @param Supplier $supplier
     * @param Warehouse $warehouse
     */
    private function createInventoryLevelsForRelatedProducts(Supplier $supplier, Warehouse $warehouse)
    {
        $entityManager = $this->doctrineHelper
            ->getEntityManagerForClass(InventoryLevel::class);
        $productSupplierRelations = $this->doctrineHelper
            ->getEntityManagerForClass(ProductSupplierRelation::class)
            ->getRepository(ProductSupplierRelation::class)
            ->findBy(['supplier' => $supplier, 'canDropship' => true]);

        foreach ($productSupplierRelations as $productSupplierRelation) {
            $this->createInventoryLevelForRelatedProduct($productSupplierRelation, $warehouse, $entityManager, false);
        }
        $entityManager->flush();
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param Warehouse $warehouse
     * @param EntityManager $entityManager
     * @param bool $flush
     */
    private function createInventoryLevelForRelatedProduct(
        ProductSupplierRelation $productSupplierRelation,
        Warehouse $warehouse,
        EntityManager $entityManager,
        $flush = true
    ) {
        $inventoryItem = $this->doctrineHelper
            ->getEntityManagerForClass(InventoryItem::class)
            ->getRepository(InventoryItem::class)
            ->findOneByProduct($productSupplierRelation->getProduct());
        $existingInvLevel = $inventoryItem->getInventoryLevel($warehouse);
        if (!$existingInvLevel) {
            $inventoryLevel = new InventoryLevel();
            $inventoryLevel
                ->setInventoryItem($inventoryItem)
                ->setWarehouse($warehouse)
                ->setInventoryQty(0)
                ->setOrganization($inventoryItem->getOrganization())
                ->setManagedInventory(false);
            $entityManager->persist($inventoryLevel);
            if ($flush) {
                $entityManager->flush($inventoryLevel);
            }
        }
    }

    /**
     * @param Warehouse $warehouse
     */
    private function removeInventoryLevelsForRelatedProducts(Warehouse $warehouse)
    {
        $this->doctrineHelper
            ->getEntityManagerForClass(InventoryLevel::class)
            ->getRepository(InventoryLevel::class)
            ->deleteForWarehouse($warehouse);
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param Warehouse $warehouse
     * @param EntityManager $entityManager
     * @param bool $flush
     */
    private function removeInventoryLevelForRelatedProduct(
        ProductSupplierRelation $productSupplierRelation,
        Warehouse $warehouse,
        EntityManager $entityManager,
        $flush = true
    ) {
        $inventoryItem = $this->doctrineHelper
            ->getEntityManagerForClass(InventoryItem::class)
            ->getRepository(InventoryItem::class)
            ->findOneByProduct($productSupplierRelation->getProduct());
        if ($inventoryItem) {
            $inventoryLevel = $inventoryItem->getInventoryLevel($warehouse);
            if ($inventoryLevel) {
                $entityManager->remove($inventoryLevel);
                if ($flush) {
                    $entityManager->flush($inventoryLevel);
                }
            }
        }
    }

    /**
     * @return Organization
     */
    protected function getOrganization()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(Organization::class)
            ->getRepository(Organization::class)
            ->getFirst();
    }
}
