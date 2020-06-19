<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Client;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\SearchRequest;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class SearchClient
{
    /** @var int */
    private const DEFAULT_CROSS_ITEM_PERCENT = 20;

    /** @var RestClientInterface */
    protected $innerClient;

    /**
     * @param RestClientInterface $innerClient
     * @param int|null $crossItemPercentPerPage
     */
    public function __construct(
        RestClientInterface $innerClient,
        int $crossItemPercentPerPage = null
    ) {
        /**
         * @todo Think about how to make the functionality,
         * that fixes the issue with data shifting between the page
         * f.e. when some record that we see on 1 current page was removed while we reading this page
         * when we call the 2nd page the 1st record from this page was the 2nd one before the record has been removed
         */
        if (null === $crossItemPercentPerPage) {
            $crossItemCountPerPage = self::DEFAULT_CROSS_ITEM_PERCENT;
        }

        $this->innerClient = $innerClient;
    }

    /**
     * @param SearchRequest $searchRequest
     * @return SearchResponseDTO
     * @throws RestException
     */
    public function search(SearchRequest $searchRequest): SearchResponseDTO
    {
        $data = $this->innerClient->getJSON(
            $searchRequest->getUrn(),
            $searchRequest->getQueryParams()
        );


        return new SearchResponseDTO(
            $data['items'],
            $data['total_count']
        );
    }
}
