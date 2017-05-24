<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\WarehouseType;

class LoadWarehouseTypeData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        'global'    => 'Global',
        'fixed'     => 'Fixed',
        'virtual'   => 'Virtual',
    ];

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            'Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseData'
        ];
    }

    /**
     * @param ObjectManager $manager
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
        foreach ($this->data as $name => $label) {
            $type = new WarehouseType($name);
            $type->setLabel($label);
            $this->manager->persist($type);
            $this->setReference('warehouse_type_'.$name, $type);
        }

        $this->manager->flush();
    }
}
