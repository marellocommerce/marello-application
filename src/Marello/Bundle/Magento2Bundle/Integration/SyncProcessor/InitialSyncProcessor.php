<?php

namespace Marello\Bundle\Magento2Bundle\Integration\SyncProcessor;

use Marello\Bundle\Magento2Bundle\Async\SyncInitialIntegrationMessage;
use Marello\Bundle\Magento2Bundle\Async\Topics;
use Marello\Bundle\Magento2Bundle\DTO\ConnectorSyncSettingsDTO;
use Marello\Bundle\Magento2Bundle\DTO\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\Integration\Connector\InitialConnectorInterface;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;

class InitialSyncProcessor extends AbstractSyncProcessor
{
    public const DEFAULT_MAX_INTERVAL_COUNT = 100;
    public const MAX_INTERVAL_COUNT_CONNECTOR_PARAMS_KEY = 'maxIntervalCount';

    /** @var MessageProducerInterface */
    protected $messageProducer;

    /**
     * @param MessageProducerInterface $messageProducer
     */
    public function setMessageProducer(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * In case when initial sync does not started yet, it failed or start sync date was changed - run initial sync.
     *
     * @param Integration $integration
     * @param bool $isForceSync
     * @throws Exception
     */
    public function scheduleInitialSyncIfRequired(Integration $integration, bool $isForceSync): void
    {
        if ($this->isInitialSyncRequired($integration, $isForceSync)) {
            $this->logger->info('Scheduling initial synchronization');

            $this->messageProducer->send(
                Topics::SYNC_INITIAL_INTEGRATION,
                new Message(
                    [
                        SyncInitialIntegrationMessage::INTEGRATION_ID => $integration->getId(),
                        SyncInitialIntegrationMessage::CONNECTOR_PARAMETERS => [
                            self::FORCE_SYNC_CONNECTOR_PARAMETER_KEY => $isForceSync
                        ]
                    ],
                    MessagePriority::VERY_LOW
                )
            );
        }
    }

    /**
     * @param Integration $integration
     * @param bool $isForceSync
     * @return bool
     * @throws \Exception
     */
    protected function isInitialSyncRequired(Integration $integration, bool $isForceSync): bool
    {
        /** @var Magento2TransportSettings $settingBag */
        $settingBag = $integration->getTransport()->getSettingsBag();
        $connectors = $this->getConnectorsToProcess(
            $integration,
            [$this, 'getInitialConnectorFilterFunction']
        );

        $connectorSyncSettingDTOs = $this->syncSettingsProvider->getConnectorSyncSettingDTOs(
            $integration,
            $connectors,
            $isForceSync
        );

        $outdatedConnectorExists = false;
        foreach ($connectorSyncSettingDTOs as $key => $connectorSyncSettingDTO) {
            if (!$this->isConnectorAllowed($connectorSyncSettingDTO->getConnector(), $integration, [])) {
                continue;
            }

            if ($connectorSyncSettingDTO->getSyncStartDateTime(false) > $settingBag->getInitialSyncStartDate(false)) {
                $outdatedConnectorExists = true;

                break;
            }
        }

        return $outdatedConnectorExists;
    }

    /**
     * {@inheritDoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        /** @var Magento2TransportSettings $settingBag */
        $settingBag = $integration->getTransport()->getSettingsBag();
        $connectors = $this->getConnectorsToProcess(
            $integration,
            [$this, 'getInitialConnectorFilterFunction']
        );

        $connectorSyncSettingDTOs = $this->syncSettingsProvider->getConnectorSyncSettingDTOs(
            $integration,
            $connectors,
            $this->isForceSync($parameters)
        );

        $maxIntervalCount = $parameters[self::MAX_INTERVAL_COUNT_CONNECTOR_PARAMS_KEY] ??
            self::DEFAULT_MAX_INTERVAL_COUNT;

        return $this->doProcessConnectors(
            $integration,
            $settingBag,
            $connectorSyncSettingDTOs,
            $maxIntervalCount
        );
    }

    /**
     * @param Integration $integration
     * @param Magento2TransportSettings $settingBag
     * @param ConnectorSyncSettingsDTO[] $connectorSyncSettingDTOs
     * @param int $maxIntervalCount
     * @return bool
     */
    protected function doProcessConnectors(
        Integration $integration,
        Magento2TransportSettings $settingBag,
        array $connectorSyncSettingDTOs,
        int $maxIntervalCount
    ): bool {
        /**
         * Process all initial connectors by date interval while there are connectors to process
         */
        $isSuccess = true;
        $intervalCount = 0;
        do {
            if ($intervalCount >= $maxIntervalCount) {
                break;
            }

            $statuses = [];
            $syncedConnectors = 0;
            foreach ($connectorSyncSettingDTOs as $key => $connectorSyncSettingDTO) {
                if (!$this->isConnectorAllowed($connectorSyncSettingDTO->getConnector(), $integration, $statuses)) {
                    continue;
                }

                if ($connectorSyncSettingDTO->getSyncStartDateTime(false) > $settingBag->getInitialSyncStartDate(false)) {
                    $syncedConnectors++;

                    $importSearchSettings = $this->convertToSearchSettingsDTO($settingBag, $connectorSyncSettingDTO);

                    $parameters[self::IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY] = $importSearchSettings;
                    $parameters[self::SALES_CHANNEL_DATA_KEY] = $connectorSyncSettingDTO->getSalesChannelId();

                    try {
                        $status = $this->processIntegrationConnector(
                            $integration,
                            $connectorSyncSettingDTO->getConnector(),
                            $parameters
                        );

                        $this->attachWebsiteIntegrationStatusIfRequired(
                            $integration,
                            $status,
                            $connectorSyncSettingDTO
                        );

                        /**
                         * Move sync date into past by interval value
                         */
                        $newConnectorSyncSettingDTO = $connectorSyncSettingDTO->createSettingsWithNewStartDate(
                            $importSearchSettings->getSyncStartDateTime(true)->sub(
                                $importSearchSettings->getSyncDateInterval()
                            )
                        );

                        $connectorSyncSettingDTOs[$key] = $newConnectorSyncSettingDTO;
                        $isSuccess = $isSuccess && $this->isIntegrationConnectorProcessSuccess($status);

                        if (!$isSuccess) {
                            break 2;
                        }

                        /**
                         * Save synced to date for connector
                         */
                        $syncedTo = $newConnectorSyncSettingDTO->getSyncStartDateTime();
                        if ($syncedTo < $settingBag->getInitialSyncStartDate(false)) {
                            $syncedTo = $settingBag->getInitialSyncStartDate(true);
                        }
                        $this->updateSyncedTo($integration, $status, $syncedTo);
                        $statuses[] = $status;
                    } catch (\Exception $e) {
                        $isSuccess = false;

                        $this->logger->critical($e->getMessage(), ['exception' => $e]);
                        break 2;
                    }
                }
            }

            $intervalCount++;
        } while ($syncedConnectors > 0);

        return $isSuccess;
    }

    /**
     * @param Magento2TransportSettings $settingBag
     * @param ConnectorSyncSettingsDTO $connectorSyncSettingDTO
     * @return ImportConnectorSearchSettingsDTO
     */
    protected function convertToSearchSettingsDTO(
        Magento2TransportSettings $settingBag,
        ConnectorSyncSettingsDTO $connectorSyncSettingDTO
    ): ImportConnectorSearchSettingsDTO {
        /**
         * Logic is next:
         * Initial sync start date - 14.01.2020 20:00:00
         * Sync interval - 1 day
         * Current start day - 14.01.2020 22:00:00
         *
         * Initial sync start date + Sync interval should be less than Current start day
         * in other case we should re-calculate sync interval because to prevent exceed the time limit in time filter
         */
        $syncStartDate = $settingBag->getInitialSyncStartDate(true);
        $syncInterval = $connectorSyncSettingDTO->getSyncDateInterval();
        $minStartDate = (clone $syncStartDate)->add($syncInterval);

        if ($minStartDate > $connectorSyncSettingDTO->getSyncStartDateTime()) {
            $syncInterval = date_diff($settingBag->getSyncStartDate(), $connectorSyncSettingDTO->getSyncStartDateTime());
            $syncInterval->invert = 0;
        }

        return new ImportConnectorSearchSettingsDTO(
            $connectorSyncSettingDTO->getSyncStartDateTime(),
            $syncInterval,
            $connectorSyncSettingDTO->getMissTimingDateInterval(),
            $connectorSyncSettingDTO->getWebsiteId() ?? ImportConnectorSearchSettingsDTO::NO_WEBSITE_ID
        );
    }

    /**
     * @param string $connector
     * @return bool
     */
    protected function getInitialConnectorFilterFunction(string $connector): bool
    {
        return strpos($connector, InitialConnectorInterface::CONNECTOR_SUFFIX) !== false;
    }
}
