<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Iterator\UpdatableSearchLoaderInterface;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\Filter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SortOrder;

/**
 * Add websiteAware filtering support
 * Add filtering by datetime
 */
class OrderIterator extends AbstractSearchIterator implements UpdatableSearchLoaderInterface
{
    /**
     * @var SearchParametersDTO
     */
    protected $searchParametersDTO;

    /**
     * @param SearchParametersDTO $searchParametersDTO
     * @return void
     */
    public function setSearchParametersDTO(SearchParametersDTO $searchParametersDTO): void
    {
        $this->searchParametersDTO = $searchParametersDTO;
    }

    /**
     * @return SearchParametersDTO
     */
    public function getSearchParametersDTO(): SearchParametersDTO
    {
        return $this->searchParametersDTO;
    }

    /**
     * @return SearchResponseDTO
     */
    protected function loadPage(): SearchResponseDTO
    {
        if ($this->firstLoaded) {
            $this->searchRequest->nextPage();

            $this->logger->info(
                '[Magento 2] Loading next page of Order search request.'
            );
        } else {
            $this->logger->info(
                '[Magento 2] Loading 1st page of Order search request.'
            );
        }

        return $this->searchClient->search($this->searchRequest);
    }

    protected function initSearchCriteria(): void
    {
        if (null === $this->searchParametersDTO) {
            throw new RuntimeException(
                sprintf(
                    '[Magento 2] Search Parameters must be specified before iterator "%s" start to loading process.',
                    OrderIterator::class
                )
            );
        }

        $searchCriteria = $this->searchRequest->getSearchCriteria();
        $searchCriteria->addFilters(
            $this->filterFactory->createFilter(
                $this->searchParametersDTO->getDateFieldName(),
                Filter::CONDITION_FROM,
                $this->searchParametersDTO->getStartDateTime()
            )
        );

        $searchCriteria->addFilters(
            $this->filterFactory->createFilter(
                $this->searchParametersDTO->getDateFieldName(),
                Filter::CONDITION_TO,
                $this->searchParametersDTO->getEndDateTime()
            )
        );

        if ($this->searchParametersDTO->getOriginStoreIds()) {
            $searchCriteria->addFilters(
                $this->filterFactory->createFilter(
                    'store_id',
                    Filter::CONDITION_IN,
                    $this->searchParametersDTO->getOriginStoreIds()
                )
            );
        }

        $searchCriteria->addSortOrder(
            new SortOrder(
                $this->searchParametersDTO->getDateFieldName(),
                $this->searchParametersDTO->isOrderDESC() ? SortOrder::DIRECTION_DESC : SortOrder::DIRECTION_ASC
            )
        );

        /**
         * @todo Fix page size assigment
         */
//        $searchCriteria->setPageSize($this->searchParametersDTO->getPageSize());

        $this->logger->info(
            '[Magento 2] Applying search criteria Order search request.',
            $searchCriteria->getSearchCriteriaParams()
        );
    }
}
