<?php

namespace Marello\Bundle\Magento2Bundle\Converter;

interface OrderStatusIdConverterInterface
{
    /**
     * Convert Marello Status ID to Magento Status ID
     *
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMarelloStatusId(string $statusId = null): ?string;

    /**
     * Convert Magento Status ID to Marello Status ID
     *
     * @param string|null $statusId
     * @return string|null
     */
    public function convertMagentoStatusId(string $statusId = null): ?string;
}
