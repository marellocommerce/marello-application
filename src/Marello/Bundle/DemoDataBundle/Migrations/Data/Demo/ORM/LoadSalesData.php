<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelGroupData as MigrationLoadSalesChannelGroupData;

class LoadSalesData extends AbstractFixture implements DependentFixtureInterface
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
            'name' => 'Sales Channel DE München',
            'code' => 'sales_channel_de_munchen',
            'type' => 'pos',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel DE Berlin',
            'code' => 'sales_channel_de_berlin',
            'type' => 'pos',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel DE Frankfurt',
            'code' => 'sales_channel_de_frankfurt',
            'type' => 'pos',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel DE Dortmund',
            'code' => 'sales_channel_de_dortmund',
            'type' => 'pos',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel US Webshop',
            'code' => 'sales_channel_us_webshop',
            'type' => 'webshop',
            'currency' => 'USD',
        ],
        [
            'name' => 'Sales Channel DE Webshop',
            'code' => 'sales_channel_de_webshop',
            'type' => 'webshop',
            'currency' => 'EUR',
        ],        [
            'name' => 'Sales Channel DE Outlet Webshop',
            'code' => 'sales_channel_de_outlet_webshop',
            'type' => 'webshop',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel FR Webshop',
            'code' => 'sales_channel_fr_webshop',
            'type' => 'webshop',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel UK Webshop',
            'code' => 'sales_channel_uk_webshop',
            'type' => 'webshop',
            'currency' => 'GBP',
        ],
        [
            'name' => 'Sales Channel DE eBay',
            'code' => 'sales_channel_de_ebay',
            'type' => 'marketplace',
            'currency' => 'EUR',
        ],
        [
            'name' => 'Sales Channel US Amazon',
            'code' => 'sales_channel_us_amazon',
            'type' => 'marketplace',
            'currency' => 'USD',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            MigrationLoadSalesChannelGroupData::class,
            LoadSalesChannelTypesData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSalesChannels();
    }

    /**
     * load and create SalesChannels
     */
    protected function loadSalesChannels()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $defaultSystemGroup = $this->manager->getRepository(SalesChannelGroup::class)->findOneBy(['system' => true]);
        $i = 1;

        foreach ($this->data as $values) {
            $channel = (new SalesChannel($values['name']))
                ->setChannelType($this->findTypeByName($values['type']))
                ->setCode($values['code'])
                ->setCurrency($values['currency'])
                ->setOwner($organization)
                ->setGroup($defaultSystemGroup);
            
            $this->manager->persist($channel);
            $this->setReference($channel->getCode(), $channel);
            $i++;
        }

        $this->manager->flush();
    }

    /**
     * @param string $name
     * @return null|SalesChannelType
     */
    private function findTypeByName($name)
    {
        $type = $this->manager
            ->getRepository(SalesChannelType::class)
            ->find($name);

        if ($type) {
            return $type;
        }

        return null;
    }
}
