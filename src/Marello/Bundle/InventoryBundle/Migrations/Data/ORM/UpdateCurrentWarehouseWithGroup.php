<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class UpdateCurrentWarehouseWithGroup extends AbstractFixture implements DependentFixtureInterface
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
            LoadWarehouseGroupData::class
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
     * update current Warehouse with WarehouseGroup
     */
    public function updateCurrentWarehouse()
    {
        $defaultWarehouse = $this->manager->getRepository(Warehouse::class)->getDefault();
        /** @var WarehouseGroup $warehouseGroup */
        $warehouseGroup = $this->getReference('warehouse_system_group');
        $defaultWarehouse->setGroup($warehouseGroup);
        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }
}
