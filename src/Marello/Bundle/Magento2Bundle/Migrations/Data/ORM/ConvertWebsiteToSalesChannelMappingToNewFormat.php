<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Data\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\Model\WebsiteToSalesChannelMapItem;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class ConvertWebsiteToSalesChannelMappingToNewFormat extends AbstractFixture implements VersionedFixtureInterface
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.1';
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Website[] $websites */
        $websites = $manager->getRepository(Website::class)->findAll();

        $websitePerSalesChannel = [];
        /** @var $website */
        foreach ($websites as $website) {
            if (!isset($websitePerSalesChannel[$website->getChannelId()])) {
                $websitePerSalesChannel[$website->getChannelId()] = new ArrayCollection();
            }

            $collection = $websitePerSalesChannel[$website->getChannelId()];
            if (!$website->getSalesChannel()) {
                return;
            }

            $websiteToSalesChannelMappingItem = new WebsiteToSalesChannelMapItem(
                $website->getOriginId(),
                $website->getName(),
                $website->getSalesChannel()->getId(),
                $website->getSalesChannel()->getName()
            );
            $collection->add($websiteToSalesChannelMappingItem);
        }

        foreach ($websitePerSalesChannel as $integrationId => $websiteToSalesChannelMapItemCollection) {
            $integration = $manager->getRepository(Channel::class)->find($integrationId);
            /** @var Magento2Transport $transport */
            $transport = $integration->getTransport();
            $transport->setWebsitesToSalesChannelMapItems($websiteToSalesChannelMapItemCollection);
        }

        $manager->flush();
    }
}
