<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Guzzle\Http\Client;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSApi
{
    const API_BASE_URL = '';

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $accessLicenseKey;

    /** @var Client */
    protected $client;

    /**
     * UPSApi constructor.
     *
     * @param ConfigManager $cm
     */
    public function __construct(ConfigManager $cm)
    {
        $this->username         = $cm->get('marello_shipping.ups_username');
        $this->password         = $cm->get('marello_shipping.ups_password');
        $this->accessLicenseKey = $cm->get('marello_shipping.ups_access_license_key');
        $this->client           = new Client(self::API_BASE_URL);
    }

    protected function getCredentials()
    {
        return [
            'UPSSecurity' => [
                'UsernameToken'      => [
                    'Username' => $this->username,
                    'Password' => $this->password,
                ],
                'ServiceAccessToken' => [
                    'AccessLicenseNumber' => $this->accessLicenseKey,
                ],
            ],
        ];
    }

    protected function post($resource, $data)
    {
        $credentials = $this->getCredentials();

        $headers = [
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods' => 'POST',
            'Access-Control-Allow-Origin'  => '*',
            'Content-Type'                 => 'application/json',
        ];

        $data = array_merge($data, $credentials);

        $request = $this->client->createRequest('POST', $resource, $headers, json_encode($data));

        $response = $request->send();

        return json_decode($response->getBody(true), true);
    }
}
