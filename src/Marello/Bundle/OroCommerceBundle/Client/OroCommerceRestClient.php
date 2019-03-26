<?php

namespace Marello\Bundle\OroCommerceBundle\Client;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\Guzzle\GuzzleRestClient;

class OroCommerceRestClient extends GuzzleRestClient implements OroCommerceRestClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function patch($resource, $data, array $headers = [], array $options = [])
    {
        return $this->performRequest('patch', $resource, [], $data, $headers, $options);
    }
}
