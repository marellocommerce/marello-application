<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData;

class LoadWarehouseChannelGroupLinkData extends AbstractFixture implements DependentFixtureInterface
{
    const ADDITIONAL_WAREHOUSE_CHANNELGROUP_LINK = 'additional_link';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadSalesChannelGroupData::class,
            LoadWarehouseGroupData::class
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $additionalLink = new WarehouseChannelGroupLink();
        $additionalLink
            ->setOrganization($organization)
            ->setWarehouseGroup($this->getReference(LoadWarehouseGroupData::ADDITIONAL_WAREHOUSE_GROUP))
            ->setSystem(false)
            ->addSalesChannelGroup($this->getReference(LoadSalesChannelGroupData::CHANNELGROUP_1_REF));

        $manager->persist($additionalLink);
        $manager->flush();

        $this->setReference(self::ADDITIONAL_WAREHOUSE_CHANNELGROUP_LINK, $additionalLink);
    }
}
