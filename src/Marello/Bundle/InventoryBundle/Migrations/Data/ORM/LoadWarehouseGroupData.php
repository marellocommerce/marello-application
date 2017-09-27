<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class LoadWarehouseGroupData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;
    
    /**
     * @var array
     */
    protected $data = [
        [
            'name' => 'System',
            'description' => 'Default system warehouses group',
            'system' => true
        ],
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
        $this->loadWarehouseGroups();
    }

    /**
     * load and create warehouse types
     */
    public function loadWarehouseGroups()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->data as $values) {
            $group = new WarehouseGroup();
            $group
                ->setName($values['name'])
                ->setDescription($values['description'])
                ->setSystem($values['system'])
                ->setOrganization($organization);

            $this->manager->persist($group);
            $this->setReference(sprintf('%s_warehouse_group', strtolower($values['name'])), $group);
        }

        $this->manager->flush();
    }
}
