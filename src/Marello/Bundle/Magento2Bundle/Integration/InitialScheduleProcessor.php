<?php

namespace Marello\Bundle\Magento2Bundle\Integration;

use Marello\Bundle\Magento2Bundle\Integration\Connector\DictionaryConnectorInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

class InitialScheduleProcessor extends SyncProcessor
{
    public const LAST_SYNC_ITEM_DATE_DATA_KEY = 'lastSyncItemDate';
    public const LAST_SYNC_ITEM_DATE_FORMAT = \DateTime::ISO8601;
    public const IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY = 'searchSetting';

    /** @var bool */
    protected $dictionaryDataLoaded = false;

    /**
     * {@inheritDoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        $this->processDictionaryConnectors($integration);

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        if (null === $callback) {
            $callback = function ($connector) {
                return strpos($connector, DictionaryConnectorInterface::DICTIONARY_CONNECTOR_SUFFIX) === false;
            };
        }

        return parent::processConnectors($integration, $parameters, $callback);
    }

    /**
     * @param Integration $integration
     */
    protected function processDictionaryConnectors(Integration $integration)
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
