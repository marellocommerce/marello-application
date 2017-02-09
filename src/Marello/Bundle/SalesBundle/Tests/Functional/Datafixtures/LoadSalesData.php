<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesData extends AbstractFixture
{
    const CHANNEL_1_REF = 'channel1';
    const CHANNEL_2_REF = 'channel2';
    const CHANNEL_3_REF = 'channel3';

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::CHANNEL_1_REF => ['name' => 'Channel-EUR', 'code' => 'chan_eur', 'type' => 'magento', 'currency' => 'EUR'],
        self::CHANNEL_2_REF => ['name' => 'Channel-USD', 'code' => 'chan_usd', 'type' => 'pos', 'currency' => 'USD'],
        self::CHANNEL_3_REF => ['name' => 'Channel-GBP', 'code' => 'chan_gbp', 'type' => 'pos', 'currency' => 'GBP'],
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

        foreach ($this->data as $ref => $values) {
            $channel = new SalesChannel($values['name']);
            $channel->setChannelType($values['type']);
            $channel->setCode($values['code']);
            $channel->setCurrency($values['currency']);
            $channel->setOwner($organization);

            $this->manager->persist($channel);
            $this->setReference($ref, $channel);
        }

        $this->manager->flush();
    }
}
