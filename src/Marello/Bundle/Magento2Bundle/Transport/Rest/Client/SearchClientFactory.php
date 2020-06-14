<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Client;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;

class SearchClientFactory
{
    /**
     * @param RestClientInterface $client
     * @param int|null $crossItemPercentPerPage
     * @return SearchClient
     */
    public function createSearchClient(RestClientInterface $client, int $crossItemPercentPerPage = null): SearchClient
    {
        return new SearchClient($client, $crossItemPercentPerPage);
    }
}
