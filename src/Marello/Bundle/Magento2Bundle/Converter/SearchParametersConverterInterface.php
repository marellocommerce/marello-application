<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

use Marello\Bundle\Magento2Bundle\DTO\ImportConnectorSearchSettingsDTO;
use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;

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
