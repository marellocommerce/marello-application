<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Connector;

use Marello\Bundle\Magento2Bundle\Converter\SearchParametersConverterInterface;
use Marello\Bundle\Magento2Bundle\DTO\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\Exception\InvalidConfigurationException;
use Marello\Bundle\Magento2Bundle\Integration\SyncProcessor\AbstractSyncProcessor;
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
        return parent::getSourceIterator();
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $this->connectorSearchSettings = $context->getOption(
            AbstractSyncProcessor::IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY
        );

        $this->validateIterator();
        $this->validateSearchSettings();

        $searchParametersDTO = $this->searchParametersConverter->convertConnectorSearchSettings(
            $this->connectorSearchSettings
        );
        $this->getSourceIterator()->setSearchParametersDTO($searchParametersDTO);
    }

    /**
     * @throws InvalidConfigurationException
     */
    protected function validateIterator(): void
    {
        if (!$this->getSourceIterator() instanceof UpdatableSearchLoaderInterface) {
            throw new InvalidConfigurationException(
                'The iterator must implement "UpdatableSearchLoaderInterface" to use in Magento2ImportConnector.'
            );
        }
    }

    /**
     * @throws InvalidConfigurationException
     */
    protected function validateSearchSettings(): void
    {
        if (!$this->connectorSearchSettings instanceof ImportConnectorSearchSettingsDTO) {
            throw new InvalidConfigurationException(
                'The configuration option "' . AbstractSyncProcessor::IMPORT_CONNECTOR_SEARCH_SETTINGS_KEY
                . '" must contain instance of "ImportConnectorSearchSettingsDTO" but other given.'
            );
        }
    }
}
