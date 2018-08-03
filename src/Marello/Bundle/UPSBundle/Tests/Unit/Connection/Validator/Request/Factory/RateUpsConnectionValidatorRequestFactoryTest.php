<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Connection\Validator\Request\Factory;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequest;
use Marello\Bundle\UPSBundle\Connection\Validator\Request\Factory\RateUpsConnectionValidatorRequestFactory;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

class RateUpsConnectionValidatorRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    const USERNAME = 'user';
    const PASS = 'pass';
    const KEY = 'key';
    const COUNTRY_CODE = 'country';
    const WEIGHT_UNIT = 'kg';

    public function testCreateByTransport()
    {
        $crypter = $this->createMock(SymmetricCrypterInterface::class);
        $crypter->expects(static::once())
            ->method('decryptData')
            ->willReturn(self::PASS);

        $transport = new UPSSettings();
        $transport->setUpsApiKey(self::KEY)
            ->setUpsApiUser(self::USERNAME)
            ->setUpsApiPassword(self::PASS)
            ->setUpsCountry(new Country(self::COUNTRY_CODE))
            ->setUpsUnitOfWeight(self::WEIGHT_UNIT)
        ;

        $expected = new UpsClientRequest([
            UpsClientRequest::FIELD_URL => 'rest/Rate',
            UpsClientRequest::FIELD_REQUEST_DATA => $this->getRequestData(),
        ]);

        $factory = new RateUpsConnectionValidatorRequestFactory($crypter);

        static::assertEquals($expected, $factory->createByTransport($transport));
    }

    /**
     * @return array
     */
    private function getRequestData()
    {
        return [
            'UPSSecurity' => [
                'UsernameToken' => [
                    'Username' => self::USERNAME,
                    'Password' => self::PASS,
                ],
                'ServiceAccessToken' => [
                    'AccessLicenseNumber' => self::KEY,
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
                            'CountryCode' => self::COUNTRY_CODE,
                        ]
                    ],
                    'ShipTo' => [
                        'Name' => 'Company1',
                        'Address' =>[
                            'PostalCode' => '0000000000000000',
                            'CountryCode' => self::COUNTRY_CODE,
                        ]
                    ],
                    'ShipFrom' => [
                        'Name' => 'Company2',
                        'Address' =>[
                            'PostalCode' => '0000000000000000',
                            'CountryCode' => self::COUNTRY_CODE,
                        ]
                    ],
                    'Package' => [
                        0 => [
                            'PackagingType' => [
                                'Code' => '02',
                            ],
                            'PackageWeight' => [
                                'UnitOfMeasurement' => [
                                    'Code' => self::WEIGHT_UNIT,
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
