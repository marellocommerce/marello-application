<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Integration\Connector\Settings\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\Integration\ContextConverter\SearchParametersConverterInterface;
use Marello\Bundle\Magento2Bundle\Integration\InitialScheduleProcessor;
use Marello\Bundle\Magento2Bundle\Integration\Provider\LastSyncProviderInterface;
use Marello\Bundle\Magento2Bundle\Iterator\UpdatableSearchLoaderInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

/**
 * @todo Think about put batch size to SearchParameters
 */
abstract class AbstractImportConnector extends AbstractConnector
{
    /** @var \DateTime|null */
    protected $lastSyncItemDateTime;

    /** @var LastSyncProviderInterface */
    protected $lastSyncProvider;

    /** @var ImportConnectorSearchSettingsDTO */
    protected $connectorSearchSettings;

    /** @var SearchParametersConverterInterface */
    protected $searchParametersConverter;

    /**
     * @param SearchParametersConverterInterface $searchParametersConverter
     */
    public function setSearchParametersConverter(SearchParametersConverterInterface $searchParametersConverter)
    {
        $this->searchParametersConverter = $searchParametersConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        $item = parent::read();
        $this->actualizeLastSyncKey($item);

        if (null === $item && $this->lastSyncItemDateTime instanceof \DateTime) {
            $this->writeLastSyncKeyToContext();
        }

        return $item;
    }

    /**
     * Rewrite return type of parent connector
     *
     * @return UpdatableSearchLoaderInterface
     */
    public function getSourceIterator()
    {
        parent::getSourceIterator();
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $this->connectorSearchSettings = $context->getOption(
            InitialScheduleProcessor::IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY
        );

        $this->validateIterator();
        $this->validateSearchSettings();

        $searchParametersDTO = $this->searchParametersConverter->convertConnectorSearchSettings(
            $this->connectorSearchSettings
        );
        $this->getSourceIterator()->setSearchParametersDTO($searchParametersDTO);
    }

    /**
     * @throws \LogicException
     */
    protected function validateIterator(): void
    {
        if (!$this->getSourceIterator() instanceof UpdatableSearchLoaderInterface) {
            throw new \LogicException(
                'The iterator must implements "' .
                UpdatableSearchLoaderInterface::class .
                '" to use in Magento2ImportConnector.'
            );
        }
    }

    /**
     * @throws \LogicException
     */
    protected function validateSearchSettings(): void
    {
        /**
         * @todo Refactor this exception
         */
        if (!$this->connectorSearchSettings instanceof ImportConnectorSearchSettingsDTO) {
            throw new \LogicException(
                'The configuration option " ' .
                InitialScheduleProcessor::IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY
                . '" must contain instance of "' .
                ImportConnectorSearchSettingsDTO::class .
                '" but other given.'
            );
        }
    }

    protected function writeLastSyncKeyToContext(): void
    {
        $this->addStatusData(
            InitialScheduleProcessor::LAST_SYNC_ITEM_DATE_DATA_KEY,
            $this->lastSyncItemDateTime->format(InitialScheduleProcessor::LAST_SYNC_ITEM_DATE_FORMAT)
        );
    }

    /**
     * @param array|null $item
     * @throws \Exception
     */
    protected function actualizeLastSyncKey(array $item = null): void
    {
        $remoteServerDateTime = $this->transport->getRemoteServerDateFromLastResponse();
        $currentLastSyncItemDateTime = $this->lastSyncItemDateTime;
        if (null === $currentLastSyncItemDateTime) {
            $currentLastSyncItemDateTime = $this->connectorSearchSettings->getSyncStartDate();
        }

        if (null !== $item) {
            $lastSyncItemDateTime = $this->lastSyncProvider->getLastSyncItemDateTime(
                $currentLastSyncItemDateTime,
                $this->getUpdateAtColumnName(),
                $this->getUpdateAtColumnFormat(),
                $item,
                $remoteServerDateTime
            );

            if ($lastSyncItemDateTime instanceof \DateTime) {
                $this->lastSyncItemDateTime = $lastSyncItemDateTime;
            }

            return;
        }

        if (null === $this->lastSyncItemDateTime) {
            $endDateTime = $this->getSourceIterator()->getSearchParametersDTO()->getEndDateTime();
            $maxDateTime = $remoteServerDateTime ?? new \DateTime('now', new \DateTimeZone('UTC'));
            // cover case, when no one item was synced
            // then just take point from what it was started (point on end date)
            if ($maxDateTime < $endDateTime) {
                $this->lastSyncItemDateTime = $maxDateTime;

                return;
            }

            $this->lastSyncItemDateTime = $endDateTime;
        }
    }

    /**
     * @return string
     */
    abstract protected function getUpdateAtColumnName(): string;

    /**
     * @return string
     */
    abstract protected function getUpdateAtColumnFormat(): string;
}
