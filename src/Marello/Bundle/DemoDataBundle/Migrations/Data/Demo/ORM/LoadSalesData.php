<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelGroupData;

class LoadSalesData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        ['name' => 'Magento Store', 'code' => 'magento_store','type' => 'magento', 'currency' => 'EUR'],
        ['name' => 'Flagship Store New York','code' => 'pos_nyc', 'type' => 'pos', 'currency' => 'USD'],
        ['name' => 'Store Washington D.C.', 'code' => 'pos_washington','type' => 'pos', 'currency' => 'USD'],
        ['name' => 'HQ','code' => 'marello_headquarters','type' => 'marello', 'currency' => 'EUR'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelGroupData::class,
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
        $i            = 1;

        foreach ($this->data as $values) {
            $channel = (new SalesChannel($values['name']))
                ->setChannelType($values['type'])
                ->setCode($values['code'])
                ->setCurrency($values['currency'])
                ->setOwner($organization)
                ->setGroup($defaultSystemGroup);
            
            $this->manager->persist($channel);
            $this->setReference('marello_sales_channel_' . $i, $channel);
            $i++;
        }

        $this->manager->flush();
    }
}
