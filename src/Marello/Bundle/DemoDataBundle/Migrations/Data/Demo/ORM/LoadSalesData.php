<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesData extends AbstractFixture
{

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadSalesChannels($manager);
    }

    protected function loadSalesChannels(ObjectManager $manager)
    {
        $channel1 = new SalesChannel('Webshop NL');
        $channel1->setOwner($manager->getRepository('OroOrganizationBundle:Organization')->findOneBy([]));
        $channel2 = new SalesChannel('Store Amsterdam');
        $channel2->setOwner($manager->getRepository('OroOrganizationBundle:Organization')->findOneBy([]));

        $manager->persist($channel1);
        $manager->persist($channel2);

        $manager->flush();

        $this->setReference('marello_sales_channel_0', $channel1);
        $this->setReference('marello_sales_channel_1', $channel2);
    }
}
