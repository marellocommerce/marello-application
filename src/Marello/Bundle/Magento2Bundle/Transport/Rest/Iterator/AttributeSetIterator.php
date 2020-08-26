<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;

class AttributeSetIterator extends AbstractSearchIterator
{
    /** @var int */
    public const DEFAULT_PAGE_SIZE = 25;

    /**
     * {@inheritDoc}
     */
    protected function loadPage(): SearchResponseDTO
    {
        if ($this->firstLoaded) {
            $this->searchRequest->nextPage();

            $this->logger->info(
                '[Magento 2] Loading next page of AttributeSet search request.'
            );
        } else {
            $this->logger->info(
                '[Magento 2] Loading 1st page of AttributeSet search request.'
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
        $searchCriteria->setPageSize(self::DEFAULT_PAGE_SIZE);

        $this->logger->info(
            '[Magento 2] Applying search criteria AttributeSet search request.',
            $searchCriteria->getSearchCriteriaParams()
        );
    }
}
