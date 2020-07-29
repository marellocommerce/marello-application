<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Request;

interface ShiftedItemsSearchRequestFactoryInterface
{
    /**
     * Prepares SearchRequest to make request on shifted elements,
     * based on currentSearchRequest and count of elements that suppose to be shifted
     *
     * @param SearchRequest $currentSearchRequest
     * @param int $countOfShiftedElements
     * @return SearchRequest
     */
    public function getSearchRequestForPreviousPage(SearchRequest $currentSearchRequest, int $countOfShiftedElements): SearchRequest;
}
