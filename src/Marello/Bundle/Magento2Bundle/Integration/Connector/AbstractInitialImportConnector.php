<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Integration\Connector\Settings\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\Integration\ContextConverter\SearchParametersConverterInterface;
use Marello\Bundle\Magento2Bundle\Integration\InitialScheduleProcessor;
use Marello\Bundle\Magento2Bundle\Iterator\UpdatableSearchLoaderInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

abstract class AbstractInitialImportConnector extends AbstractConnector
{
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
}
