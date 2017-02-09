<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesDataTest extends AbstractFixture
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
        $i            = 1;

        foreach ($this->data as $values) {
            $channel = new SalesChannel($values['name']);
            $channel->setChannelType($values['type']);
            $channel->setCode($values['code']);
            $channel->setCurrency($values['currency']);
            $channel->setOwner($organization);

            $this->manager->persist($channel);
            $this->setReference('marello_sales_channel_' . $i, $channel);
            $i++;
        }

        $this->manager->flush();
    }
}
