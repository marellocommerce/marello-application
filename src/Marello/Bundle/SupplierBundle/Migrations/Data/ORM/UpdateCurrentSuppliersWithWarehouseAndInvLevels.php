<?php

namespace Marello\Bundle\SupplierBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\AddExternalWarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

class UpdateCurrentSuppliersWithWarehouseAndInvLevels extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /** @var Warehouse $warehouse */
    protected $warehouse;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            AddExternalWarehouseType::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentSuppliers();
    }

    /**
     * Persist current suppliers that support dropshipping
     * in order to create external warehouse
     */
    public function updateCurrentSuppliers()
    {
        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findBy(['canDropship' => true]);

        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $warehouse = $this->getWarehouse($supplier);
            if (!$warehouse) {
                $warehouse = $this->createWarehouse($supplier);
            }
            $this->createInventoryLevelsForRelatedProducts($supplier, $warehouse);
        }
        $this->manager->flush();
    }

    /**
     * @param Supplier $supplier
     * @param Warehouse $warehouse
     */
    private function createInventoryLevelsForRelatedProducts(Supplier $supplier, Warehouse $warehouse)
    {
        $productSupplierRelations = $this->manager
            ->getRepository(ProductSupplierRelation::class)
            ->findBy(['supplier' => $supplier, 'canDropship' => true]);

        foreach ($productSupplierRelations as $productSupplierRelation) {
            $this->createInventoryLevelForRelatedProduct($productSupplierRelation, $warehouse);
        }

        $this->manager->flush();
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function getWarehouse(Supplier $supplier)
    {
        if ($this->warehouse === null) {
            $warehouseType = $this->manager
                ->getRepository(WarehouseType::class)
                ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
            $warehouse = $this->manager
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
        $warehouseType = $this->manager
            ->getRepository(WarehouseType::class)
            ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);

        $warehouse = new Warehouse(sprintf('%s External Warehouse', $supplier->getName()));
        $warehouse
            ->setAddress(clone $supplier->getAddress())
            ->setCode($supplier->getCode())
            ->setWarehouseType($warehouseType);
        if ($organization = $supplier->getOrganization()) {
            $warehouse->setOwner($organization);
        }

        $this->manager->persist($warehouse);
        $this->manager->flush();

        return $warehouse;
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param Warehouse $warehouse
     */
    private function createInventoryLevelForRelatedProduct(
        ProductSupplierRelation $productSupplierRelation,
        Warehouse $warehouse
    ) {
        $inventoryItem = $this->manager
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
            $this->manager->persist($inventoryLevel);
        }
    }
}
