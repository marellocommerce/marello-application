<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Request;

class ShiftedItemsSearchRequestFactory implements ShiftedItemsSearchRequestFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getSearchRequestForPreviousPage(SearchRequest $currentSearchRequest, int $countOfShiftedElements): SearchRequest
    {
        $newSearchRequest = clone $currentSearchRequest;
        $currentPageSize = $currentSearchRequest->getSearchCriteria()->getPageSize();
        $previousPageNumber = $currentSearchRequest->getSearchCriteria()->getPageNumber() - 1;

        $increment = -1;
        $newPageNumber = $previousPageNumber;
        $possiblePageSize = $countOfShiftedElements < $currentPageSize ? $countOfShiftedElements : $currentPageSize;

        do {
            $increment++;
            $newPageSize = $possiblePageSize + $increment;
            if ($currentPageSize % $newPageSize === 0) {
                $newPageNumber = $currentPageSize / $newPageSize * $previousPageNumber;
                break;
            }
        } while($possiblePageSize < $currentPageSize);

        $newSearchRequest->getSearchCriteria()->setPageSize($newPageSize);
        $newSearchRequest->getSearchCriteria()->setPageNumber($newPageNumber);

        return $newSearchRequest;
    }
}
