<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria;

/**
 * https://devdocs.magento.com/guides/v2.3/rest/performing-searches.html
 *
 * Search criteria has next rules:
 * - One filter group can have up to 2 filters
 * - Two filters in one filter group is logical OR
 * - Filters in different filters group is logical AND
 * - You can only search top-level attributes.
 */
class SearchCriteria
{
    /** @var int */
    public const DEFAULT_PAGE_SIZE = 10;

    /** @var FilterGroup[] */
    protected $filterGroups = [];

    /** @var SortOrder[]  */
    protected $sortOrders = [];

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $currentPage;

    /**
     * @param int $pageSize
     * @param int $currentPage
     */
    public function __construct(int $pageSize = self::DEFAULT_PAGE_SIZE, int $currentPage = 1)
    {
        $this->pageSize = $pageSize;
        $this->currentPage = $currentPage;
    }

    /**
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return $this
     */
    public function nextPage(): self
    {
        ++$this->currentPage;

        return $this;
    }

    /**
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param Filter $filter
     * @param Filter|null $orFilter
     * @return $this
     */
    public function addFilters(Filter $filter, Filter $orFilter = null): self
    {
        $filterGroup = new FilterGroup();
        $filterGroup->addFilter($filter);

        if (null !== $orFilter) {
            $filterGroup->addFilter($orFilter);
        }

        $this->filterGroups[] = $filterGroup;

        return $this;
    }

    /**
     * @param SortOrder $sortOrder
     * @return $this
     */
    public function addSortOrder(SortOrder $sortOrder): self
    {
        $this->sortOrders[] = $sortOrder;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterGroups(): array
    {
        return $this->filterGroups;
    }

    /**
     * @param FilterGroup $filterGroup
     * @return $this
     */
    public function removeFilterGroup(FilterGroup $filterGroup): self
    {
        $this->filterGroups = \array_filter(
            $this->filterGroups, function (FilterGroup $checkedFilterGroup) use ($filterGroup) {
                return $checkedFilterGroup !== $filterGroup;
        });

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchCriteriaParams(): array
    {
        $searchCriteriaParams = [
            'pageSize' => $this->pageSize,
            'currentPage' => $this->currentPage
        ];

        if (!empty($this->filterGroups)) {
            $searchCriteriaParams['filterGroups'] = \array_map(function (FilterGroup $filterGroup) {
                return $filterGroup->getFilterGroupParams();
            }, $this->filterGroups);
        }

        if (!empty($this->sortOrders)) {
            $searchCriteriaParams['sortOrders'] = \array_map(function (SortOrder $sortOrder) {
                return $sortOrder->getSortOrderParams();
            }, $this->sortOrders);
        }

        return [
            'searchCriteria' => $searchCriteriaParams
        ];
    }
}
