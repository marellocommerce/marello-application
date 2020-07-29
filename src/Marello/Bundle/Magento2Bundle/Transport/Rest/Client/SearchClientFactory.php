<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Client;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;

class SearchClientFactory
{
    /**
     * @param RestClientInterface $client
     * @return SearchClient
     */
    public function createSearchClient(RestClientInterface $client): SearchClient
    {
        return new SearchClient($client);
    }
}
