<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class UpdateCurrentWarehouseWithType extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            'Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseTypeData'
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
        $warehouseType = $this->getReference('warehouse_type_global');
        $defaultWarehouse->setWarehouseType($warehouseType);
        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }
}
