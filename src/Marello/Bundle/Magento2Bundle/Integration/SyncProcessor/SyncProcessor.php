<?php

namespace Marello\Bundle\Magento2Bundle\Integration\SyncProcessor;

use Marello\Bundle\Magento2Bundle\DTO\ConnectorSyncSettingsDTO;
use Marello\Bundle\Magento2Bundle\DTO\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\Integration\Connector\DictionaryConnectorInterface;
use Marello\Bundle\Magento2Bundle\Integration\Connector\InitialConnectorInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class SyncProcessor extends AbstractSyncProcessor
{
    /** @var bool */
    protected $dictionaryDataLoaded = false;

    /** @var InitialSyncProcessor */
    protected $initialSyncProcessor;

    /**
     * @param InitialSyncProcessor $initialSyncProcessor
     */
    public function setInitialSyncProcessor(InitialSyncProcessor $initialSyncProcessor)
    {
        $this->initialSyncProcessor = $initialSyncProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        if (!$this->isIntegrationEnabled($integration)) {
            return false;
        }

        $this->processDictionaryConnectors($integration);
        /** @var Integration $integration */
        $integration = $this->reloadIntegrationEntity($integration);
        if (!$integration) {
            $this->logger->error(
                '[Magento 2] Cannot process synchronization, because integration is not exist.',
                [
                    'integration_id' => $integration->getId()
                ]
            );
        }

        $this->initialSyncProcessor->scheduleInitialSyncIfRequired($integration, $this->isForceSync($parameters));

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        if (null === $callback) {
            $callback = function ($connector) {
                return strpos($connector, DictionaryConnectorInterface::CONNECTOR_SUFFIX) === false &&
                    strpos($connector, InitialConnectorInterface::CONNECTOR_SUFFIX) === false;
            };
        }

        $connectors = $this->getConnectorsToProcess($integration, $callback);
        $connectorSyncSettingDTOs = $this->syncSettingsProvider->getConnectorSyncSettingDTOs(
            $integration,
            $connectors,
            $this->isForceSync($parameters)
        );

        $statuses = [];
        $isSuccess = true;
        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        foreach ($connectorSyncSettingDTOs as $key => $connectorSyncSettingDTO) {
            if (!$this->isConnectorAllowed($connectorSyncSettingDTO->getConnector(), $integration, $statuses)) {
                continue;
            }

            if ($connectorSyncSettingDTO->getSyncStartDateTime() < $currentDateTime) {
                $importSearchSettings = $this->convertToSearchSettingsDTO($currentDateTime, $connectorSyncSettingDTO);

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

                    $isSuccess = $this->isIntegrationConnectorProcessSuccess($status);
                    if (!$isSuccess) {
                        break;
                    }

                    /**
                     * Move sync date into future by interval value
                     */
                    $newStartDate = $importSearchSettings->getSyncStartDateTime(true)->add(
                        $importSearchSettings->getSyncDateInterval()
                    );
                    /**
                     * Save synced to date for connector
                     */
                    $this->updateSyncedTo($integration, $status, $newStartDate);
                    $statuses[] = $status;
                } catch (\Exception $e) {
                    $isSuccess = false;

                    $this->logger->critical($e->getMessage(), ['exception' => $e]);
                    break;
                }
            }
        }

        return $isSuccess;
    }

    /**
     * @param \DateTime $currentDateTime
     * @param ConnectorSyncSettingsDTO $connectorSyncSettingDTO
     * @return ImportConnectorSearchSettingsDTO
     */
    protected function convertToSearchSettingsDTO(
        \DateTime $currentDateTime,
        ConnectorSyncSettingsDTO $connectorSyncSettingDTO
    ): ImportConnectorSearchSettingsDTO {
        $maxEndDateTime = clone $currentDateTime;
        $syncInterval = $connectorSyncSettingDTO->getSyncDateInterval();
        $maxStartDate = (clone $maxEndDateTime)->sub($syncInterval);
        /**
         * Logic is next:
         * Current date - 14.01.2020 22:00:00
         * Sync interval - 1 day
         * Current start day - 14.01.2020 20:00:00
         *
         * Current date - Sync interval should be bigger than Current start day
         * in other case we should re-calculate sync interval to prevent use future time in time filter
         */
        if ($maxStartDate < $connectorSyncSettingDTO->getSyncStartDateTime()) {
            $syncInterval = date_diff($maxEndDateTime, $connectorSyncSettingDTO->getSyncStartDateTime());
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
     * @param Integration $integration
     */
    protected function processDictionaryConnectors(Integration $integration): void
    {
        if ($this->dictionaryDataLoaded) {
            return;
        }

        /** @var ConnectorInterface[] $dictionaryConnectors */
        $dictionaryConnectors = $this->registry->getRegisteredConnectorsTypes(
            $integration->getType(),
            function (ConnectorInterface $connector) {
                return $connector instanceof DictionaryConnectorInterface;
            }
        );

        foreach ($dictionaryConnectors as $connector) {
            $this->processIntegrationConnector($integration, $connector);
        }

        $this->dictionaryDataLoaded = true;
    }
}
