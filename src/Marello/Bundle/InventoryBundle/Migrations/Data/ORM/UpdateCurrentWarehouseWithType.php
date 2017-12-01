<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;

class UpdateCurrentWarehouseWithType extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadWarehouseTypeData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCurrentWarehouse();
    }

    /**
     * update current Warehouse with the WarehouseType
     */
    public function updateCurrentWarehouse()
    {
        $defaultWarehouse = $this->manager->getRepository(Warehouse::class)->getDefault();
        /** @var WarehouseType $warehouseType */
        $warehouseType = $this->getReference('warehouse_type_global');
        $defaultWarehouse->setWarehouseType($warehouseType);
        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }
}
