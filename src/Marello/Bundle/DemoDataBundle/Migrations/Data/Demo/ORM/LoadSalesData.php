<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        array('name' => 'Magento Store', 'type' => 'magento'),
        array('name' => 'Flagship Store New York', 'type' => 'pos'),
        array('name' => 'Store Washington D.C.', 'type' => 'pos'),
        array('name' => 'HQ', 'type' => 'marello'),
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadSalesChannels($manager);
    }

    protected function loadSalesChannels(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getOrganizationById(1);
        $i = 0;
        foreach ($this->data as $values) {
            $channel = new SalesChannel($values['name']);
            $channel->setChannelType($values['type']);
            $channel->setOwner($organization);

            $manager->persist($channel);
            $this->setReference('marello_sales_channel_'.$i, $channel);
            $i++;
        }

        $manager->flush();
    }
}
