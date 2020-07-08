<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;

interface FilterFactoryInterface
{
    /**
     * @param string $fieldName
     * @param string $conditionType
     * @param $searchValue
     * @param string|null $searchValueFormat
     * @param array $searchValueContext
     * @return Filter
     *
     * @throws RuntimeException
     */
    public function createFilter(
        string $fieldName,
        string $conditionType,
        $searchValue,
        string $searchValueFormat = null,
        array $searchValueContext = []
    ): Filter;
}
