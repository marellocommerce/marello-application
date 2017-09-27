<?php

namespace Marello\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class LoadSalesChannelGroupData extends AbstractFixture
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
            'description' => 'Default system sales channels group',
            'type' => 'marello',
            'system' => true
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSalesChannelGroups();
    }

    /**
     * load and create SalesChannels
     */
    protected function loadSalesChannelGroups()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->data as $values) {
            $group = new SalesChannelGroup();
            $group
                ->setName($values['name'])
                ->setDescription($values['description'])
                ->setSystem($values['system'])
                ->setOrganization($organization);

            $this->manager->persist($group);
        }

        $this->manager->flush();
    }
}
