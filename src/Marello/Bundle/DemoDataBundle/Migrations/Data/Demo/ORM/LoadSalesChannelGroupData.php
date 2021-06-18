<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
            'description' => 'Europe Sales Channel group',
            'system' => false,
            'channels' => [
                'sales_channel_de_webshop',
                'sales_channel_fr_webshop',
                'sales_channel_uk_webshop',
                'sales_channel_de_outlet_webshop'
            ]
        ],
        [
            'name' => 'US Group',
            'description' => 'US Sales Channel group',
            'system' => false,
            'channels' => [
                'sales_channel_us_webshop'
            ]
        ],
        [
            'name' => 'Sales Channel DE Berlin',
            'description' => 'Sales Channel DE Berlin group',
            'system' => false,
            'channels' => [
                'sales_channel_de_berlin'
            ]
        ],
        [
            'name' => 'Sales Channel DE Frankfurt',
            'description' => 'Sales Channel DE Frankfurt group',
            'system' => false,
            'channels' => [
                'sales_channel_de_frankfurt'
            ]
        ],
        [
            'name' => 'Sales Channel DE München',
            'description' => 'Sales Channel DE München group',
            'system' => false,
            'channels' => [
                'sales_channel_de_munchen'
            ]
        ],
        [
            'name' => 'Sales Channel DE Dortmund',
            'description' => 'Sales Channel DE Dortmund group',
            'system' => false,
            'channels' => [
                'sales_channel_de_dortmund'
            ]
        ],
        [
            'name' => 'Sales Channel DE eBay',
            'description' => 'Sales Channel DE eBay group',
            'system' => false,
            'channels' => [
                'sales_channel_de_ebay'
            ]
        ],
        [
            'name' => 'Sales Channel US Amazon',
            'description' => 'Sales Channel US Amazon group',
            'system' => false,
            'channels' => [
                'sales_channel_us_amazon'
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
                ->setName($values['name'])
                ->setDescription($values['description'])
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
