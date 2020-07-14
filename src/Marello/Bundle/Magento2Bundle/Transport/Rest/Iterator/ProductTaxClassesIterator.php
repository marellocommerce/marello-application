<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\ProductTaxClassDataConverter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\Filter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SortOrder;

class ProductTaxClassesIterator extends AbstractSearchIterator
{
    /**
     * {@inheritDoc}
     */
    protected function loadPage(): SearchResponseDTO
    {
        if ($this->firstLoaded) {
            $this->searchRequest->nextPage();

            $this->logger->info(
                '[Magento 2] Loading next page of ProductTaxClasses search request.'
            );
        } else {
            $this->logger->info(
                '[Magento 2] Loading 1st page of ProductTaxClasses search request.'
            );
        }

        return $this->searchClient->search($this->searchRequest);
    }

    /**
     * {@inheritDoc}
     */
    protected function initSearchCriteria(): void
    {
        $searchCriteria = $this->searchRequest->getSearchCriteria();
        $searchCriteria->addFilters(
            $this->filterFactory->createFilter(
                ProductTaxClassDataConverter::MAGENTO_FIELD_TAX_TYPE_NAME,
                Filter::CONDITION_EQ,
                'PRODUCT'
            )
        );
        $searchCriteria->addSortOrder(
            new SortOrder(
                ProductTaxClassDataConverter::MAGENTO_FIELD_ID_NAME,
                SortOrder::DIRECTION_ASC
            )
        );
        $searchCriteria->setPageSize(self::DEFAULT_PAGE_SIZE);

        $this->logger->info(
            '[Magento 2] Applying search criteria ProductTaxClasses search request.',
            $searchCriteria->getSearchCriteriaParams()
        );
    }
}
