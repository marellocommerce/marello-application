<?php

namespace Marello\Bundle\Magento2Bundle\Integration\ContextConverter;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\Integration\Connector\Settings\ImportConnectorSearchSettingsDTO;

interface SearchParametersConverterInterface
{
    /**
     * @param ImportConnectorSearchSettingsDTO $connectorSearchSettingsDTO
     * @return SearchParametersDTO
     */
    public function convertConnectorSearchSettings(
        ImportConnectorSearchSettingsDTO $connectorSearchSettingsDTO
    ): SearchParametersDTO;
}
