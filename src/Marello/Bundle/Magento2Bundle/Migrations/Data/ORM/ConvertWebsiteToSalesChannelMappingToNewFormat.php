<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\Magento2Bundle\DTO\WebsiteToSalesChannelMappingItemDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ConvertWebsiteToSalesChannelMappingToNewFormat extends AbstractFixture
{
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
                $websitePerSalesChannel[$website->getChannelId()] = [];
            }

            if (!$website->getSalesChannel()) {
                return;
            }

            $websitePerSalesChannel[$website->getChannelId()][] = (new WebsiteToSalesChannelMappingItemDTO(
                [
                    'originWebsiteId' => $website->getOriginId(),
                    'websiteName' => $website->getName(),
                    'salesChannelId' => $website->getSalesChannel()->getId(),
                    'salesChannelName' => $website->getSalesChannel()->getName()
                ]
            ))->getData();
        }

        foreach ($websitePerSalesChannel as $integrationId => $websiteToSalesChannelMappingData) {
            $integration = $manager->getRepository(Channel::class)->find($integrationId);
            $integration->getTransport()->setWebsiteToSalesChannelMapping(
                $websiteToSalesChannelMappingData
            );
        }

        $manager->flush();
    }
}
