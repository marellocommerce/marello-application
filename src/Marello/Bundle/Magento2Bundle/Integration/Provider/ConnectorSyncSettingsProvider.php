<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Provider;

use Marello\Bundle\Magento2Bundle\DependencyInjection\Configuration;
use Marello\Bundle\Magento2Bundle\DTO\ConnectorSyncSettingsDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteIntegrationStatusRepository;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Integration\Connector\SingleWebsiteConnectorInterface;
use Marello\Bundle\Magento2Bundle\Integration\SyncProcessor\AbstractSyncProcessor;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\ForceConnectorInterface;

class ConnectorSyncSettingsProvider implements ConnectorSyncSettingsProviderInterface
{
    /** @var TrackedSalesChannelProvider */
    protected $trackedSalesChannelProvider;

    /** @var ConfigManager */
    protected $configManager;

    /** @var IntegrationRepository  */
    protected $integrationRepository;

    /** @var WebsiteIntegrationStatusRepository */
    protected $websiteIntegrationStatusRepository;

    /**
     * @param TrackedSalesChannelProvider $trackedSalesChannelProvider
     * @param ConfigManager $configManager
     * @param IntegrationRepository $integrationRepository
     * @param WebsiteIntegrationStatusRepository $websiteIntegrationStatusRepository
     */
    public function __construct(
        TrackedSalesChannelProvider $trackedSalesChannelProvider,
        ConfigManager $configManager,
        IntegrationRepository $integrationRepository,
        WebsiteIntegrationStatusRepository $websiteIntegrationStatusRepository
    ) {
        $this->trackedSalesChannelProvider = $trackedSalesChannelProvider;
        $this->configManager = $configManager;
        $this->integrationRepository = $integrationRepository;
        $this->websiteIntegrationStatusRepository = $websiteIntegrationStatusRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnectorSyncSettingDTOs(
        Integration $integration,
        array $connectors,
        bool $forceSync = false
    ): array {
        if ($integration->getType() !== Magento2ChannelType::TYPE) {
            throw new RuntimeException(
                'Invalid Integration given, service accept integration with type "magento2" only.'
            );
        }

        $connectorSyncSettings = [];
        $salesChannelInfosPerIntegration = $this->trackedSalesChannelProvider->getSalesChannelInfosByIntegrationId(
            $integration->getId()
        );

        foreach ($connectors as $connector) {
            if ($forceSync &&
                (!$connector instanceof ForceConnectorInterface || false === $connector->supportsForceSync())) {
                continue;
            }

            if ($connector instanceof SingleWebsiteConnectorInterface) {
                /**
                 * In case if no websites attached to integration we skip sync for connector
                 */
                if (empty($salesChannelInfosPerIntegration)) {
                    continue;
                }

                foreach ($salesChannelInfosPerIntegration as $salesChannelInfo) {
                    $connectorSyncSettings[$connector->getType() . ':' . $salesChannelInfo->getWebsiteId()] =
                        $this->getConnectorSettingDTOforSingleWebsiteConnector(
                            $integration,
                            $connector,
                            $salesChannelInfo,
                            $forceSync
                        );
                }
            } else {
                $connectorList[$connector->getType()] = $this->getConnectorSettingDTO(
                    $integration,
                    $connector,
                    $forceSync
                );
            }
        }

        return $connectorSyncSettings;
    }

    /**
     * @param Integration $integration
     * @param ConnectorInterface $connector
     * @param SalesChannelInfo $salesChannelInfo
     * @param bool $forceSync
     * @return ConnectorSyncSettingsDTO
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getConnectorSettingDTOforSingleWebsiteConnector(
        Integration $integration,
        ConnectorInterface $connector,
        SalesChannelInfo $salesChannelInfo,
        bool $forceSync
    ): ConnectorSyncSettingsDTO {
        $syncToDateTime = $this->getDefaultSyncStartDate($integration);
        if (!$forceSync) {
            $websiteLastStatus = $this->websiteIntegrationStatusRepository->getLastWebsiteStatusForConnector(
                $integration,
                $connector->getType(),
                $salesChannelInfo->getWebsiteId(),
                Status::STATUS_COMPLETED
            );

            $integrationStatus = $websiteLastStatus ? $websiteLastStatus->getInnerStatus() : null;

            $syncToDateTime = $this->getSyncToDateTimeByStatus($integration, $integrationStatus);
        }

        return new ConnectorSyncSettingsDTO(
            $connector,
            $syncToDateTime,
            $this->getImportSyncInterval(),
            $this->getMissTimingAssumptionInterval(),
            $salesChannelInfo
        );
    }

    /**
     * @param Integration $integration
     * @param ConnectorInterface $connector
     * @param bool $forceSync
     * @return ConnectorSyncSettingsDTO
     */
    protected function getConnectorSettingDTO(
        Integration $integration,
        ConnectorInterface $connector,
        bool $forceSync
    ): ConnectorSyncSettingsDTO {
        $syncToDateTime = $this->getDefaultSyncStartDate($integration);
        if (!$forceSync) {
            $lastStatus = $this->integrationRepository->getLastStatusForConnector(
                $integration,
                $connector->getType(),
                Status::STATUS_COMPLETED
            );

            $syncToDateTime = $this->getSyncToDateTimeByStatus($integration, $lastStatus);
        }

        return new ConnectorSyncSettingsDTO(
            $connector,
            $syncToDateTime,
            $this->getImportSyncInterval(),
            $this->getMissTimingAssumptionInterval()
        );
    }

    /**
     * @param Integration $integration
     * @param Status|null $status
     * @return \DateTime
     */
    protected function getSyncToDateTimeByStatus(Integration $integration, Status $status = null): \DateTime
    {
        if (null === $status) {
            return $this->getDefaultSyncStartDate($integration);
        }

        $statusData = $status->getData();
        if (!empty($statusData[AbstractSyncProcessor::SYNC_TO_DATE_TIME_DATA_KEY])) {
            return \DateTime::createFromFormat(
                AbstractSyncProcessor::SYNC_TO_DATE_TIME_FORMAT,
                $statusData[AbstractSyncProcessor::SYNC_TO_DATE_TIME_DATA_KEY],
                new \DateTimeZone('UTC')
            );
        }

        return $this->getDefaultSyncStartDate($integration);
    }

    /**
     * @param Integration $integration
     * @return \DateTime
     */
    protected function getDefaultSyncStartDate(Integration $integration): \DateTime
    {
        /** @var Magento2TransportSettings $settingBag */
        $settingBag = $integration->getTransport()->getSettingsBag();

        return $settingBag->getSyncStartDate();
    }

    /**
     * @return \DateInterval
     */
    protected function getImportSyncInterval(): \DateInterval
    {
        $importStepIntervalString = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::IMPORT_SYNC_INTERVAL_KEY)
        );

        return new \DateInterval($importStepIntervalString);
    }

    /**
     * @return \DateInterval
     */
    protected function getMissTimingAssumptionInterval(): \DateInterval
    {
        $missTimingAssumptionIntervalString = $this->configManager->get(
            Configuration::getConfigKeyByName(Configuration::MISS_TIMING_ASSUMPTION_INTERVAL_KEY)
        );

        return new \DateInterval($missTimingAssumptionIntervalString);
    }
}
