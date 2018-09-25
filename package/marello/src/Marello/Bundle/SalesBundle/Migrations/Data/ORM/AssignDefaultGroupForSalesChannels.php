<?php

namespace Marello\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

class AssignDefaultGroupForSalesChannels extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelData::class,
            LoadSalesChannelGroupData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $salesChannels = $manager->getRepository(SalesChannel::class)->findAll();
        $defaultSystemGroup = $manager
            ->getRepository(SalesChannelGroup::class)
            ->findSystemChannelGroup();
        foreach ($salesChannels as $salesChannel) {
            $salesChannel->setGroup($defaultSystemGroup);
            $manager->persist($salesChannel);
        }
        $manager->flush();
    }
}
