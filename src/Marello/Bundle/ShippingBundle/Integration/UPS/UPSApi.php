<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Guzzle\Http\Client;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class UPSApi
{
    const API_BASE_URL         = 'https://onlinetools.ups.com/json';
    const TESTING_API_BASE_URL = 'https://wwwcie.ups.com/json';

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
     * @param bool          $useTestingApi
     */
    public function __construct(ConfigManager $cm, $useTestingApi = false)
    {
        $this->username         = $cm->get('marello_shipping.ups_username');
        $this->password         = $cm->get('marello_shipping.ups_password');
        $this->accessLicenseKey = $cm->get('marello_shipping.ups_access_license_key');
        $this->client           = new Client($useTestingApi ? self::TESTING_API_BASE_URL : self::API_BASE_URL);
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

        $result = json_decode($response->getBody(true), true);

        if (array_key_exists('Error', $result)) {
            throw (new UPSApiException($result['Error']['Description'], $result['Error']['Code']))
                ->setResult($result);
        }

        if (array_key_exists('Fault', $result)) {
            throw (new UPSApiException($result['Fault']['faultstring']))
                ->setResult($result);
        }

        return $result;
    }
}
