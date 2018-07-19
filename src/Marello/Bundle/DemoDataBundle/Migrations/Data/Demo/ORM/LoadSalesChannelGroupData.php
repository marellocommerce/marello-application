<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class LoadSalesChannelGroupData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array $data
     */
    protected $data = [
        [
            'name' => 'Europe Group',
            'description' => 'Europe Sales Channel Group',
            'system' => false,
            'channels' => [
                'sales_channel_de_webshop',
                'sales_channel_fr_webshop',
                'sales_channel_uk_webshop',
            ]
        ],
        [
            'name' => 'US Group',
            'description' => 'US Sales Channel Group',
            'system' => false,
            'channels' => [
                'sales_channel_us_webshop'
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesData::class,
        ];
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

            $this->addSalesChannels($group, $values['channels']);
            $this->createWarehouseChannelGroupLink($group);
            $this->setReference($values['name'], $group);
        }
        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSalesChannelGroups();
    }

    /**
     * Add WarehouseChanelGroupLink to SalesChannel group since listener will only
     * add them to the WHG Link if application is installed
     * @param SalesChannelGroup $channelGroup
     */
    private function createWarehouseChannelGroupLink(SalesChannelGroup $channelGroup)
    {
        /** @var WarehouseChannelGroupLink $systemWarehouseChannelGroupLink */
        $systemWarehouseChannelGroupLink = $this->manager
            ->getRepository(WarehouseChannelGroupLink::class)
            ->findSystemLink();

        if ($systemWarehouseChannelGroupLink) {
            $systemWarehouseChannelGroupLink->addSalesChannelGroup($channelGroup);
            $this->manager->persist($systemWarehouseChannelGroupLink);
        }
    }
    /**
     * Add sales channels to group
     * @param SalesChannelGroup $group
     * @param $channelCodes
     */
    private function addSalesChannels(SalesChannelGroup $group, $channelCodes)
    {
        $channels = $this->getSalesChannels($channelCodes);
        foreach ($channels as $channel) {
            /** @var SalesChannel $channel */
            $group->addSalesChannel($channel);
        }
        $this->manager->flush();
    }

    /**
     * Get sales channels based on channel codes
     * @param array $channelCodes
     * @return array
     */
    private function getSalesChannels(array $channelCodes)
    {
        $channels = [];
        foreach ($channelCodes as $channelCode) {
            $channels[] = $this->getReference($channelCode);
        }

        return $channels;
    }
}
