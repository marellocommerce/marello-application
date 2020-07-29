<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Client\SearchClient;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\SearchRequest;
use Marello\Bundle\Magento2Bundle\Transport\Rest\SearchCriteria\FilterFactoryInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractSearchIterator implements LoggerAwareInterface, \Iterator, \Countable
{
    use LoggerAwareTrait;

    /** @var int */
    public const DEFAULT_PAGE_SIZE = 25;

    /** @var SearchClient */
    protected $searchClient;

    /** @var SearchRequest */
    protected $searchRequest;

    /** @var FilterFactoryInterface */
    protected $filterFactory;

    /** @var bool */
    protected $firstLoaded = false;

    /**
     * Results of page data
     *
     * @var array
     */
    protected $rows = [];

    /**
     * Total count of items in response
     *
     * @var int
     */
    protected $totalCount = null;

    /**
     * Offset of current item in current page
     *
     * @var int
     */
    protected $offset = -1;

    /**
     * A position of a current item within the current page
     *
     * @var int
     */
    protected $position = -1;

    /**
     * Current item, populated from request response
     *
     * @var mixed
     */
    protected $current = null;

    /**
     * @param SearchClient $searchClient
     * @param SearchRequest $searchRequest
     * @param FilterFactoryInterface $filterFactory
     */
    public function __construct(
        SearchClient $searchClient,
        SearchRequest $searchRequest,
        FilterFactoryInterface $filterFactory
    ) {
        $this->searchClient = $searchClient;
        $this->searchRequest = $searchRequest;
        $this->filterFactory = $filterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->firstLoaded) {
            $this->rewind();
        }

        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->offset++;

        if (!isset($this->rows[$this->offset]) && !$this->loadNextPage()) {
            $this->current = null;
        } else {
            $this->current = $this->rows[$this->offset];
        }
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (!$this->firstLoaded) {
            $this->rewind();
        }

        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (!$this->firstLoaded) {
            $this->rewind();
        }

        return null !== $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->firstLoaded = false;
        $this->totalCount = null;
        $this->offset = -1;
        $this->position = -1;
        $this->current = null;
        $this->rows = [];

        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (!$this->firstLoaded) {
            $this->rewind();
        }

        return $this->totalCount;
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize(int $pageSize): void
    {
        $searchCriteria = $this->searchRequest->getSearchCriteria();
        $searchCriteria->setPageSize($pageSize);
    }

    /**
     * Load page
     *
     * @return SearchResponseDTO
     * @throws RestException
     */
    abstract protected function loadPage(): SearchResponseDTO;

    /**
     * Prepares search criteria and set it to the Search Request
     */
    abstract protected function initSearchCriteria(): void;

    /**
     * Attempts to load next page
     *
     * @return bool If page loaded successfully
     */
    protected function loadNextPage()
    {
        if (false === $this->firstLoaded) {
            $this->initSearchCriteria();
        }

        if (true === $this->firstLoaded && $this->position + 1 >= $this->totalCount) {
            return false;
        }

        $searchResponse = $this->loadPage();
        $this->processSearchResponseDTO($searchResponse);

        return count($this->rows) > 0;
    }

    /**
     * Fill properties with information from the new page
     *
     * @param SearchResponseDTO $searchResponseDTO
     */
    protected function processSearchResponseDTO(SearchResponseDTO $searchResponseDTO): void
    {
        $this->firstLoaded = true;
        $this->rows = $searchResponseDTO->getItems();
        $this->totalCount = $searchResponseDTO->getTotalCount();
        $this->offset = 0;
    }
}
