<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;

class LoadWarehouseTypeData extends AbstractFixture implements DependentFixtureInterface
{
    const GLOBAL_TYPE = 'global';
    const FIXED_TYPE = 'fixed';
    const VIRTUAL_TYPE = 'virtual';
    
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::GLOBAL_TYPE,
        self::FIXED_TYPE,
        self::VIRTUAL_TYPE,
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
