<?php

namespace Marello\Bundle\Magento2Bundle\Integration\SyncProcessor;

use Marello\Bundle\Magento2Bundle\DTO\ConnectorSyncSettingsDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\Entity\WebsiteIntegrationStatus;
use Marello\Bundle\Magento2Bundle\Integration\Provider\ConnectorSyncSettingsProviderInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

abstract class AbstractSyncProcessor extends SyncProcessor
{
    public const FORCE_SYNC_CONNECTOR_PARAMETER_KEY = 'force';
    public const SALES_CHANNEL_DATA_KEY = 'salesChannel';
    public const SYNC_TO_DATE_TIME_DATA_KEY = 'syncToDateTime';
    public const SYNC_TO_DATE_TIME_FORMAT = \DateTime::ISO8601;
    public const LAST_SYNC_ITEM_DATE_TIME_DATA_KEY = 'lastSyncItemDateTime';
    public const LAST_SYNC_ITEM_DATE_TIME_FORMAT = \DateTime::ISO8601;
    public const IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY = 'searchSetting';

    /** @var ConnectorSyncSettingsProviderInterface */
    protected $syncSettingsProvider;

    /**
     * @param ConnectorSyncSettingsProviderInterface $syncSettingsProvider
     */
    public function setConnectorSyncSettingsProvider(ConnectorSyncSettingsProviderInterface $syncSettingsProvider)
    {
        $this->syncSettingsProvider = $syncSettingsProvider;
    }

    /**
     * @param Integration $integration
     * @return bool
     */
    protected function isIntegrationEnabled(Integration $integration): bool
    {
        if (!$integration->isEnabled()) {
            $this->logger->error(
                '[Magento 2] Cannot process synchronization, because integration is not enabled.',
                [
                    'integration_id' => $integration->getId()
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param Integration $integration
     * @param Status $status
     * @param ConnectorSyncSettingsDTO $connectorSyncSettingsDTO
     */
    protected function attachWebsiteIntegrationStatusIfRequired(
        Integration $integration,
        Status $status,
        ConnectorSyncSettingsDTO $connectorSyncSettingsDTO
    ) {
        if (null === $connectorSyncSettingsDTO->getWebsiteId()) {
            return;
        }

        $websiteIntegrationStatusManager = $this->doctrineRegistry
            ->getManagerForClass(WebsiteIntegrationStatus::class);

        $website = $this->doctrineRegistry
            ->getManagerForClass(Website::class)
            ->getReference(Website::class, $connectorSyncSettingsDTO->getWebsiteId());

        $websiteIntegrationStatus = new WebsiteIntegrationStatus();
        $websiteIntegrationStatus->setInnerStatus($status);
        $websiteIntegrationStatus->setWebsite($website);

        $websiteIntegrationStatusManager
            ->getRepository(WebsiteIntegrationStatus::class)
            ->addStatusAndFlush($integration, $websiteIntegrationStatus);
    }

    /**
     * @param Integration $integration
     * @param Status $status
     * @param \DateTime $syncedTo
     */
    protected function updateSyncedTo(Integration $integration, Status $status, \DateTime $syncedTo)
    {
        $statusData = $status->getData();
        $statusData[self::SYNC_TO_DATE_TIME_DATA_KEY] = $syncedTo->format(self::SYNC_TO_DATE_TIME_FORMAT);
        $status->setData($statusData);

        $this->addConnectorStatusAndFlush($integration, $status);
    }

    /**
     * @param Integration $integration
     * @return Integration|null
     */
    protected function reloadIntegrationEntity(Integration $integration): ?Integration
    {
        return $this->doctrineRegistry
            ->getManagerForClass(Integration::class)
            ->find(Integration::class, $integration->getId());
    }

    /**
     * @param array $connectorParameters
     * @return bool
     */
    protected function isForceSync(array $connectorParameters): bool
    {
        return $connectorParameters[self::FORCE_SYNC_CONNECTOR_PARAMETER_KEY] ?? false;
    }
}
