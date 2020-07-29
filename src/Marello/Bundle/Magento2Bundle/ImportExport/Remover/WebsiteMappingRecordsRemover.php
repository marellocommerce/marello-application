<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Remover;

use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class WebsiteMappingRecordsRemover extends NonExistedRecordsRemover
{
    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
        parent::write($items);

        $transport = $this->getTransport();
        if (null === $transport) {
            return;
        }

        $websiteOriginIds = $this->getWebsiteOriginIds();
        /**
         * Skip empty list of $websiteOriginIds, because it's probably issue in sync
         */
        if (empty($websiteOriginIds)) {
            return;
        }

        $this->processSavingNewWebsiteToSchMapping($transport, $websiteOriginIds);
    }

    /**
     * @param Magento2Transport $transport
     * @param array $websiteOriginIds
     */
    protected function processSavingNewWebsiteToSchMapping(Magento2Transport $transport, array $websiteOriginIds)
    {
        $newWebsiteToSchMapping = $transport->getSettingsBag()->getMappingItemArrayContainWebsiteOriginId(
            $websiteOriginIds
        );

        if (count($transport->getWebsiteToSalesChannelMapping()) === count($newWebsiteToSchMapping)) {
            return;
        }

        $transport->setWebsiteToSalesChannelMapping($newWebsiteToSchMapping);
        $em = $this->registry->getManagerForClass(Magento2Transport::class);
        $em->flush();
        $em->clear();
    }

    /**
     * @return Magento2Transport|null
     */
    protected function getTransport(): ?Magento2Transport
    {
        $channelId = $this->getChannelId();
        if (null === $channelId) {
            return null;
        }

        $channel = $this->registry
            ->getManagerForClass(Channel::class)
            ->find(Channel::class, $channelId);

        if (null === $channel) {
            return null;
        }

        $transport = $channel->getTransport();
        if ($transport instanceof Magento2Transport) {
            return $transport;
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getWebsiteOriginIds(): array
    {
        $channelId = $this->getChannelId();
        if (null === $channelId) {
            return [];
        }

        /** @var WebsiteRepository $websiteRepository */
        $websiteRepository = $this->registry
            ->getManagerForClass(Website::class)
            ->getRepository(Website::class);

        return $websiteRepository->getOriginIdsByIntegrationId($channelId);
    }
}
