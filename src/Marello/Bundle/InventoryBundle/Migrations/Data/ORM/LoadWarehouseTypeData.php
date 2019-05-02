<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class LoadWarehouseTypeData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED,
        WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
        WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadWarehouseData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadWarehouseTypes();
    }

    /**
     * load and create warehouse types
     */
    public function loadWarehouseTypes()
    {
        foreach ($this->data as $name) {
            $type = new WarehouseType($name);
            $type->setLabel(ucfirst($name));
            $this->manager->persist($type);
            $this->setReference('warehouse_type_'.$name, $type);
        }

        $this->manager->flush();
    }
}
