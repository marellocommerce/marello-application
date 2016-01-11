<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesData extends AbstractFixture
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        ['name' => 'Magento Store', 'type' => 'magento'],
        ['name' => 'Flagship Store New York', 'type' => 'pos'],
        ['name' => 'Store Washington D.C.', 'type' => 'pos'],
        ['name' => 'HQ', 'type' => 'marello'],
    ];

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
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getOrganizationById(1);
        $i            = 1;
        foreach ($this->data as $values) {
            $channel = new SalesChannel($values['name']);
            $channel->setChannelType($values['type']);
            $channel->setOwner($organization);

            $this->manager->persist($channel);
            $this->setReference('marello_sales_channel_' . $i, $channel);
            $i++;
        }

        $this->manager->flush();
    }
}
