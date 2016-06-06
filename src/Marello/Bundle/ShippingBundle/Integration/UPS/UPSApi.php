<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Guzzle\Http\Client;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSApi
{
    /** @var Client */
    protected $client;

    /**
     * UPSApi constructor.
     *
     * @param ConfigManager $cm
     * @param bool          $useTestingApi
     */
    public function __construct(ConfigManager $cm, $useTestingApi = false)
    {
        $this->client = new Client($cm->get('marello_shipping.ups_api_base_url'));
    }

    public function post($resource, $body)
    {
        $headers = [
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods' => 'POST',
            'Access-Control-Allow-Origin'  => '*',
            'Content-Type'                 => 'Application/x-www-form-urlencoded',
        ];

        $request = $this->client->createRequest('POST', $resource, $headers, $body);

        $response = $request->send();

        return $response->getBody(true);
    }
}
