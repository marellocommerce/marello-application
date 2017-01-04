<?php

namespace Marello\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class LoadSalesChannelData extends AbstractFixture
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        ['name' => 'Main','code' => 'main','type' => 'marello', 'currency' => 'EUR'],
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
        $localization = $this->manager->getRepository('OroLocaleBundle:Localization')->find(1);
        $i            = 1;

        foreach ($this->data as $values) {
            $channel = new SalesChannel($values['name']);
            $channel->setChannelType($values['type']);
            $channel->setCode($values['code']);
            $channel->setCurrency($values['currency']);
            $channel->setOwner($organization);
            $channel->setLocalization($localization);
            $channel->setLocale('nl_NL');

            $this->manager->persist($channel);
            $i++;
        }

        $this->manager->flush();
    }
}
