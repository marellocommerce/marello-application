<?php

namespace Marello\Bundle\UPSBundle\Connection\Validator\Request\Factory;

use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequest;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

class RateUpsConnectionValidatorRequestFactory implements UpsConnectionValidatorRequestFactoryInterface
{
    /**
     * @internal
     */
    const REQUEST_URL = 'rest/Rate';

    /**
     * @var SymmetricCrypterInterface
     */
    private $crypter;

    /**
     * @param SymmetricCrypterInterface $crypter
     */
    public function __construct(SymmetricCrypterInterface $crypter)
    {
        $this->crypter = $crypter;
    }

    /**
     * {@inheritDoc}
     */
    public function createByTransport(UPSSettings $transport)
    {
        return new UpsClientRequest([
            UpsClientRequest::FIELD_URL => self::REQUEST_URL,
            UpsClientRequest::FIELD_REQUEST_DATA => $this->getRequestData($transport),
        ]);
    }

    /**
     * @param UPSSettings $transport
     *
     * @return array
     */
    private function getRequestData(UPSSettings $transport)
    {
        return [
            'UPSSecurity' => [
                'UsernameToken' => [
                    'Username' => $transport->getUpsApiUser(),
                    'Password' => $this->crypter->decryptData($transport->getUpsApiPassword()),
                ],
                'ServiceAccessToken' => [
                    'AccessLicenseNumber' => $transport->getUpsApiKey(),
                ],
            ],
            'RateRequest' => [
                'Request' => [
                    'RequestOption' => 'Shop',
                ],
                'Shipment' => [
                    'Shipper' => [
                        'Name' => 'Company2',
                        'Address' => [
                            'PostalCode' => '0000000000000000',
                            'CountryCode' => $transport->getUpsCountry()->getIso2Code(),
                        ]
                    ],
                    'ShipTo' => [
                        'Name' => 'Company1',
                        'Address' =>[
                            'PostalCode' => '0000000000000000',
                            'CountryCode' => $transport->getUpsCountry()->getIso2Code(),
                        ]
                    ],
                    'ShipFrom' => [
                        'Name' => 'Company2',
                        'Address' =>[
                            'PostalCode' => '0000000000000000',
                            'CountryCode' => $transport->getUpsCountry()->getIso2Code(),
                        ]
                    ],
                    'Package' => [
                        0 => [
                            'PackagingType' => [
                                'Code' => '02',
                            ],
                            'PackageWeight' => [
                                'UnitOfMeasurement' => [
                                    'Code' => $transport->getUpsUnitOfWeight(),
                                ],
                                'Weight' => '10',
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
