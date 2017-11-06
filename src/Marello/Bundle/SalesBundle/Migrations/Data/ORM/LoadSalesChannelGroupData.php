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
            'name' => 'System Group',
            'description' => 'System Sales Channel Group',
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
                ->setName(
                    sprintf('%s %s', $organization->getName(), $values['name'])
                )
                ->setDescription(sprintf('%s for %s organization', $values['description'], $organization->getName()))
                ->setSystem($values['system'])
                ->setOrganization($organization);

            $this->manager->persist($group);
        }

        $this->manager->flush();
    }
}
