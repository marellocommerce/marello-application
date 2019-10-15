<?php

namespace Marello\Bundle\OroCommerceBundle\Client;

use Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Client;
use Marello\Bundle\OroCommerceBundle\Client\Guzzle\Http\Url;
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

    /**
     * Build URL
     *
     * @param string $resource
     * @param array $params
     * @return string
     */
    protected function buildUrl($resource, array $params)
    {
        if (filter_var($resource, FILTER_VALIDATE_URL)) {
            $path = $resource;
        } else {
            $path = rtrim($this->baseUrl, '/') . '/' . ltrim($resource, '/');
        }

        $url = Url::factory($path);

        foreach ($params as $name => $value) {
            $url->getQuery()->add($name, $value);
        }

        return (string)$url;
    }

    /**
     * @return Client
     */
    protected function getGuzzleClient()
    {
        if (!$this->guzzleClient) {
            $this->guzzleClient = new Client();
        }
        return $this->guzzleClient;
    }
}
