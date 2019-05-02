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
            'name' => 'System Group',
            'description' => 'System Warehouse Group',
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
                ->setName(
                    sprintf('%s %s', $organization->getName(), $values['name'])
                )
                ->setDescription(sprintf('%s for %s organization', $values['description'], $organization->getName()))
                ->setSystem($values['system'])
                ->setOrganization($organization);

            $this->manager->persist($group);
            $this->setReference(sprintf('warehouse_%s', str_replace(' ', '_', strtolower($values['name']))), $group);
        }

        $this->manager->flush();
    }
}
