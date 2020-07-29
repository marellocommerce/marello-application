<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Client;

use Marello\Bundle\Magento2Bundle\DTO\SearchResponseDTO;
use Marello\Bundle\Magento2Bundle\Transport\Rest\Request\SearchRequest;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class SearchClient
{
    /** @var RestClientInterface */
    protected $innerClient;

    /**
     * @param RestClientInterface $innerClient
     */
    public function __construct(RestClientInterface $innerClient)
    {
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
