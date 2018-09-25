<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Request\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Provider\AddressCoordinatesProviderInerface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory\DistanceMatrixApiRequestFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequest;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DistanceMatrixApiRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var AddressCoordinatesProviderInerface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coordinatesProvider;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var DistanceMatrixApiRequestFactory
     */
    protected $distanceMatrixApiRequestFactory;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->coordinatesProvider = $this->getMockBuilder(AddressCoordinatesProviderInerface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->distanceMatrixApiRequestFactory = new DistanceMatrixApiRequestFactory(
            $this->configManager,
            $this->coordinatesProvider
        );
    }

    /**
     * @dataProvider createRequestDataProvider
     *
     * @param float $originLat
     * @param float $originLon
     * @param float $destinationLat
     * @param float $destinationLon
     * @param string $travelMode
     * @param array $expectedParams
     */
    public function testCreateRequest(
        $originLat,
        $originLon,
        $destinationLat,
        $destinationLon,
        $travelMode,
        array $expectedParams
    ) {
        /** @var GoogleApiContextInterface|\PHPUnit_Framework_MockObject_MockObject $context **/
        $context = $this->createMock(GoogleApiContextInterface::class);

        $originAddress = new MarelloAddress();
        $destinationAddress = new MarelloAddress();

        $context
            ->expects(static::once())
            ->method('getOriginAddress')
            ->willReturn($originAddress);
        $context
            ->expects(static::once())
            ->method('getDestinationAddress')
            ->willReturn($destinationAddress);

        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(DistanceMatrixApiRequestFactory::MODE_CONFIG_FIELD)
            ->willReturn($travelMode);

        $this->coordinatesProvider
            ->expects(static::exactly(2))
            ->method('getCoordinates')
            ->willReturnOnConsecutiveCalls(
                [
                    GeocodingApiResultFactory::LATITUDE => $originLat,
                    GeocodingApiResultFactory::LONGITUDE => $originLon
                ],
                [
                    GeocodingApiResultFactory::LATITUDE => $destinationLat,
                    GeocodingApiResultFactory::LONGITUDE => $destinationLon
                ]
            );
        $expectedRequest = new GoogleApiRequest([GoogleApiRequest::FIELD_REQUEST_PARAMETERS => $expectedParams]);
        $actualRequest =  $this->distanceMatrixApiRequestFactory->createRequest($context);
        static::assertEquals($expectedRequest, $actualRequest);
    }

    /**
     * @return array
     */
    public function createRequestDataProvider()
    {
        return [
            [
                'originLat' => 0,
                'originLon' => 0,
                'destinationLat' => 100,
                'destinationLon' => 100,
                'travelMode' => 'driving',
                'expectedParams' =>  [
                    DistanceMatrixApiRequestFactory::UNITS => 'metric',
                    DistanceMatrixApiRequestFactory::MODE => 'driving',
                    DistanceMatrixApiRequestFactory::ORIGINS => '0,0',
                    DistanceMatrixApiRequestFactory::DESTINATIONS => '100,100'
                ]
            ],
            [
                'originLat' => 10,
                'originLon' => 10,
                'destinationLat' => 200,
                'destinationLon' => 200,
                'travelMode' => 'walking',
                'expectedParams' =>  [
                    DistanceMatrixApiRequestFactory::UNITS => 'metric',
                    DistanceMatrixApiRequestFactory::MODE => 'walking',
                    DistanceMatrixApiRequestFactory::ORIGINS => '10,10',
                    DistanceMatrixApiRequestFactory::DESTINATIONS => '200,200'
                ]
            ]
        ];
    }
}
