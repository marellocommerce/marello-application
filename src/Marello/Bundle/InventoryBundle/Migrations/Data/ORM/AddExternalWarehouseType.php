<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class AddExternalWarehouseType extends AbstractFixture implements DependentFixtureInterface
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
        $this->addExternalWarehouseType();
    }

    public function addExternalWarehouseType()
    {
        $name = WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL;
        $externalWarehouse = $this->manager
            ->getRepository(WarehouseType::class)
            ->find($name);
        /** @var WarehouseType $warehouseType */
        if (!$externalWarehouse) {
            $type = new WarehouseType($name);
            $type->setLabel(ucfirst($name));
            $this->manager->persist($type);
            $this->setReference('warehouse_type_'.$name, $type);
            $this->manager->flush();
        }
    }
}
