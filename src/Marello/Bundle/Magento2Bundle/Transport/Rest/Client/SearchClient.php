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

        /**
         * @todo Think about how to make the functionality,
         * that fixes the issue with data shifting between the page
         * f.e. when some record that we see on 1 current page was removed while we reading this page
         * when we call the 2nd page the 1st record from this page was the 2nd one before the record has been removed
         */
        return new SearchResponseDTO(
            $data['items'],
            $data['total_count']
        );
    }
}
