<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Request\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory\GeocodingApiRequestFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequest;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Component\Testing\Unit\EntityTrait;

class GeocodingApiRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var GeocodingApiRequestFactory
     */
    protected $geocodingApiRequestFactory;

    protected function setUp()
    {
        $this->geocodingApiRequestFactory = new GeocodingApiRequestFactory();
    }

    /**
     * @dataProvider createRequestDataProvider
     *
     * @param array $addressParams
     * @param array $expectedParams
     */
    public function testCreateRequest(array $addressParams, array $expectedParams)
    {
        $address = $this->getEntity(MarelloAddress::class, $addressParams);
        /** @var GoogleApiContextInterface|\PHPUnit_Framework_MockObject_MockObject $context **/
        $context = $this->createMock(GoogleApiContextInterface::class);
        $context->expects(static::once())
            ->method('getOriginAddress')
            ->willReturn($address);

        $expectedRequest = new GoogleApiRequest([GoogleApiRequest::FIELD_REQUEST_PARAMETERS => $expectedParams]);
        $actualRequest = $this->geocodingApiRequestFactory->createRequest($context);
        static::assertEquals($expectedRequest, $actualRequest);
    }

    /**
     * @return array
     */
    public function createRequestDataProvider()
    {
        $iso2Code = 'iso2Code';
        $regionCode = 'RegionCode';
        $country = $this->getEntity(Country::class, ['iso2Code' => $iso2Code]);
        $region = $this->getEntity(Region::class, ['code' => $regionCode]);
        $postalCode = 12345;
        $city = 'Test City';
        $street = 'Test Street';
        $street2 = 'Test Street2';
        return [
            'all' => [
                'addressParams' => [
                    'country' => $country,
                    'region' => $region,
                    'postalCode' => $postalCode,
                    'city' => $city,
                    'street' => $street,
                    'street2' => $street2
                ],
                'expectedParams' => [
                    GeocodingApiRequestFactory::COMPONENTS => [
                        GeocodingApiRequestFactory::COMPONENT_COUNTRY => $iso2Code,
                        GeocodingApiRequestFactory::COMPONENT_ADMINISTRATIVE_AREA => $regionCode,
                        GeocodingApiRequestFactory::COMPONENT_POSTAL_CODE => $postalCode
                    ],
                    GeocodingApiRequestFactory::ADDRESS => 'Test Street Test Street2 Test City'
                ]
            ],
            'noStreet2' => [
                'addressParams' => [
                    'country' => $country,
                    'region' => $region,
                    'postalCode' => $postalCode,
                    'city' => $city,
                    'street' => $street
                ],
                'expectedParams' => [
                    GeocodingApiRequestFactory::COMPONENTS => [
                        GeocodingApiRequestFactory::COMPONENT_COUNTRY => $iso2Code,
                        GeocodingApiRequestFactory::COMPONENT_ADMINISTRATIVE_AREA => $regionCode,
                        GeocodingApiRequestFactory::COMPONENT_POSTAL_CODE => $postalCode
                    ],
                    GeocodingApiRequestFactory::ADDRESS => 'Test Street Test City'
                ]
            ],
            'noAddress' => [
                'addressParams' => [
                    'country' => $country,
                    'region' => $region,
                    'postalCode' => $postalCode
                ],
                'expectedParams' => [
                    GeocodingApiRequestFactory::COMPONENTS => [
                        GeocodingApiRequestFactory::COMPONENT_COUNTRY => $iso2Code,
                        GeocodingApiRequestFactory::COMPONENT_ADMINISTRATIVE_AREA => $regionCode,
                        GeocodingApiRequestFactory::COMPONENT_POSTAL_CODE => $postalCode
                    ]
                ]
            ],
            'noParameters' => [
                'addressParams' => [],
                'expectedParams' => []
            ]
        ];
    }
}
