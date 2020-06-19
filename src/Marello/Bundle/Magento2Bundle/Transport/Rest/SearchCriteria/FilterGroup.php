<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

class FilterGroup
{
    /** @var Filter[] */
    protected $filters = [];

    /**
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getFilterGroupParams(): array
    {
        $filtersParams = \array_map(function (Filter $filter) {
            return $filter->getFilterParams();
        }, $this->filters);

        return [
            'filters' => $filtersParams
        ];
    }
}
