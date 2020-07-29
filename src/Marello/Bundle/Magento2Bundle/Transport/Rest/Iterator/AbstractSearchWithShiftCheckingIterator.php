<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Client\SearchClient;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\SearchRequest;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\ShiftedItemsSearchRequestFactoryInterface;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\FilterFactoryInterface;

abstract class AbstractSearchWithShiftCheckingIterator extends AbstractSearchIterator
{
    /** @var ShiftedItemsSearchRequestFactoryInterface */
    protected $shiftedItemsSearchRequestFactory;

    /**
     * @param SearchClient $searchClient
     * @param SearchRequest $searchRequest
     * @param FilterFactoryInterface $filterFactory
     * @param ShiftedItemsSearchRequestFactoryInterface $shiftedItemsSearchRequestFactory
     */
    public function __construct(
        SearchClient $searchClient,
        SearchRequest $searchRequest,
        FilterFactoryInterface $filterFactory,
        ShiftedItemsSearchRequestFactoryInterface $shiftedItemsSearchRequestFactory
    ) {
        parent::__construct($searchClient, $searchRequest, $filterFactory);
        $this->shiftedItemsSearchRequestFactory = $shiftedItemsSearchRequestFactory;
    }

    /**
     * Allow to check data items not shifted between the page,
     * this can occurs when item was removed or updated and you use field "updated_at" to filter records.
     * In this case the updated records goes to the end of the item list and all records that located
     * after updated item, change its position on minus one. In result the 1st item from the next page,
     * goes to the place of last item of the current page. And you won't get it within current sync process.
     *
     * {@inheritDoc}
     */
    protected function processSearchResponseDTO(SearchResponseDTO $searchResponseDTO): void
    {
        $shiftedItems = [];
        if ($this->doNeedToCheckOnExistenceOfShiftedItems($searchResponseDTO)) {
            $shiftedItems = $this->getShiftedItems($searchResponseDTO);
        }

        parent::processSearchResponseDTO($searchResponseDTO);

        if (!empty($shiftedItems)) {
            $this->logger->info(
                '[Magento 2] Found shifted items, it will be added to the current row set.',
                [
                    'shiftedItemsCount' => count($shiftedItems)
                ]
            );

            \array_unshift($this->rows, ...$shiftedItems);
            /**
             * Move position on 2 element per one shifted,
             * because we need take into account that updated element and shifted elements.
             */
            $this->position -= count($shiftedItems) * 2;
        } else {
            $this->logger->info(
                '[Magento 2] Shifted items hasn\'t found. Continue loading next page.'
            );
        }
    }

    /**
     * @param SearchResponseDTO $searchResponseDTO
     * @return bool
     */
    protected function doNeedToCheckOnExistenceOfShiftedItems(SearchResponseDTO $searchResponseDTO): bool
    {
        if (false === $this->firstLoaded) {
            return false;
        }

        $countOfShiftedElement = $this->totalCount - $searchResponseDTO->getTotalCount();
        return $countOfShiftedElement > 0;
    }

    /**
     * @param SearchResponseDTO $searchResponseDTO
     * @return array
     */
    protected function getShiftedItems(SearchResponseDTO $searchResponseDTO): array
    {
        $countOfShiftedElements = $this->totalCount - $searchResponseDTO->getTotalCount();
        $searchRequest = $this->shiftedItemsSearchRequestFactory->getSearchRequestForPreviousPage(
            $this->searchRequest,
            $countOfShiftedElements
        );

        $this->logger->info(
            '[Magento 2] Check shifted items within previous search request. Applying next query params.',
            [
                'queryParams' => $searchRequest->getQueryParams()
            ]
        );

        $searchResponse = $this->searchClient->search($searchRequest);
        $items = $searchResponse->getItems();

        if (empty($items)) {
            return [];
        }

        $shiftedItems = [];
        $idColumnName = $this->getIdColumnNameToCompareReadItems();
        $itemsToCheckReverse = \array_reverse($items, false);
        $existedItemsReverse = \array_reverse($this->rows, false);
        foreach ($itemsToCheckReverse as $index => $itemToCheck) {
            $existedItemIdValue = $existedItemsReverse[$index][$idColumnName] ?? null;
            $itemToCheckIdValue = $itemsToCheckReverse[$index][$idColumnName] ?? null;
            if (null === $existedItemIdValue || null === $itemToCheckIdValue) {
                break;
            }

            if ($existedItemIdValue !== $itemToCheckIdValue) {
                $shiftedItems[] = $itemToCheck;
            } else {
                break;
            }
        }

        return $shiftedItems;
    }

    /**
     * @return string
     */
    abstract protected function getIdColumnNameToCompareReadItems(): string;
}
