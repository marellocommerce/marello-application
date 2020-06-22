<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class LoadSalesChannelGroupData extends AbstractFixture implements DependentFixtureInterface
{
    const CHANNELGROUP_1_REF = 'channelgroup1';
    const CHANNELGROUP_2_REF = 'channelgroup2';
    const CHANNELGROUP_3_REF = 'channelgroup3';
    const CHANNELGROUP_4_REF = 'channelgroup4';

    /**
     * @var ObjectManager $manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::CHANNELGROUP_1_REF => [
            'description' => 'First Channel Group',
            'channels' => [LoadSalesData::CHANNEL_1_REF]
        ],
        self::CHANNELGROUP_2_REF => [
            'description' => 'Second Channel Group',
            'channels' => [LoadSalesData::CHANNEL_2_REF]
        ],
        self::CHANNELGROUP_3_REF => [
            'description' => 'Third Channel Group',
            'channels' => [LoadSalesData::CHANNEL_3_REF]
        ],
        self::CHANNELGROUP_4_REF => [
            'description' => 'Fourth Channel Group',
            'channels' => [LoadSalesData::CHANNEL_4_REF]
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesData::class
        ];
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
     * load and create SalesChannels
     */
    protected function loadSalesChannelGroups()
    {
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->data as $ref => $values) {
            $channelGroup = $this->buildChannelGroup($ref, $values);
            $channelGroup
                ->setOrganization($organization);

            $this->manager->persist($channelGroup);
            $this->setReference($ref, $channelGroup);
        }

        $this->manager->flush();
    }

    /**
     * @param string $reference
     * @param array  $data
     *
     * @return SalesChannelGroup
     */
    private function buildChannelGroup($reference, $data)
    {
        $channelGroup = new SalesChannelGroup($reference);

        $channelGroup->setName($reference)
            ->setDescription($data['description'])
            ->setSystem(false);
        foreach ($data['channels'] as $channel) {
            $channelGroup->addSalesChannel($this->getReference($channel));
        }
        
        return $channelGroup;
    }
}
