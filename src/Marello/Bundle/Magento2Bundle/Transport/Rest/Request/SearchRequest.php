<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Request;

use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SearchCriteria;

class SearchRequest extends Request
{
    /** @var SearchCriteria */
    protected $searchCriteria;

    /**
     * @param SearchCriteria $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteria $searchCriteria): self
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * @return SearchCriteria
     */
    public function getSearchCriteria(): SearchCriteria
    {
        $this->tryInitDefaultSearchCriteria();

        return $this->searchCriteria;
    }

    /**
     * @return $this
     */
    public function nextPage(): self
    {
        $this->tryInitDefaultSearchCriteria();

        $this->searchCriteria->nextPage();

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        $this->tryInitDefaultSearchCriteria();

        return $this->searchCriteria->getSearchCriteriaParams();
    }

    /**
     * @return SearchRequest
     */
    protected function tryInitDefaultSearchCriteria(): self
    {
        if (null === $this->searchCriteria) {
            $this->searchCriteria = new SearchCriteria();
        }

        return $this;
    }

    public function __clone()
    {
        $this->searchCriteria = clone $this->searchCriteria;
    }
}
