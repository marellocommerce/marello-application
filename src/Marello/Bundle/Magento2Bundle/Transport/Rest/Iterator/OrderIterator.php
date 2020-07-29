<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;
use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Exception\InvalidConfigurationException;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\MagentoOrderDataConverter;
use Marello\Bundle\Magento2Bundle\Iterator\UpdatableSearchLoaderInterface;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\Filter;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\SortOrder;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class OrderIterator extends AbstractSearchWithShiftCheckingIterator implements UpdatableSearchLoaderInterface
{
    /** @var int */
    public const DEFAULT_PAGE_SIZE = 10;

    /** @var SearchParametersDTO */
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
     * @throws RestException
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

    /**
     * @throws InvalidConfigurationException
     */
    protected function initSearchCriteria(): void
    {
        if (null === $this->searchParametersDTO) {
            throw new InvalidConfigurationException(
                'Search Parameters must be specified before iterator "OrderIterator" start to loading process.'
            );
        }

        $searchCriteria = $this->searchRequest->getSearchCriteria();
        $searchCriteria->addFilters(
            $this->filterFactory->createFilter(
                $this->getDateFilterColumnName(),
                Filter::CONDITION_FROM,
                $this->searchParametersDTO->getStartDateTime()
            )
        );

        $searchCriteria->addFilters(
            $this->filterFactory->createFilter(
                $this->getDateFilterColumnName(),
                Filter::CONDITION_TO,
                $this->searchParametersDTO->getEndDateTime()
            )
        );

        if ($this->searchParametersDTO->getOriginStoreIds()) {
            $searchCriteria->addFilters(
                $this->filterFactory->createFilter(
                    MagentoOrderDataConverter::STORE_ID_COLUMN_NAME,
                    Filter::CONDITION_IN,
                    $this->searchParametersDTO->getOriginStoreIds()
                )
            );
        }

        $searchCriteria->addSortOrder(
            new SortOrder($this->getDateSortColumnName(), $this->getDateSortOrder())
        );

        $searchCriteria->setPageSize(self::DEFAULT_PAGE_SIZE);

        $this->logger->info(
            '[Magento 2] Applying search criteria Order search request.',
            [
                'searchCriteriaParams' => $searchCriteria->getSearchCriteriaParams()
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getIdColumnNameToCompareReadItems(): string
    {
        return MagentoOrderDataConverter::ID_COLUMN_NAME;
    }

    /**
     * {@inheritDoc}
     */
    protected function doNeedToCheckOnExistenceOfShiftedItems(SearchResponseDTO $searchResponseDTO): bool
    {
        return parent::doNeedToCheckOnExistenceOfShiftedItems($searchResponseDTO) &&
            false === $this->searchParametersDTO->isInitialMode();
    }

    /**
     * @return string
     */
    protected function getDateFilterColumnName(): string
    {
        return $this->searchParametersDTO->isInitialMode() ?
            MagentoOrderDataConverter::CREATED_AT_COLUMN_NAME :
            MagentoOrderDataConverter::UPDATED_AT_COLUMN_NAME;
    }

    /**
     * @return string
     */
    protected function getDateSortColumnName(): string
    {
        return $this->searchParametersDTO->isInitialMode() ?
            MagentoOrderDataConverter::CREATED_AT_COLUMN_NAME :
            MagentoOrderDataConverter::UPDATED_AT_COLUMN_NAME;
    }

    /**
     * @return string
     */
    protected function getDateSortOrder(): string
    {
        return $this->searchParametersDTO->isInitialMode() ?
            SortOrder::DIRECTION_DESC :
            SortOrder::DIRECTION_ASC;
    }
}
