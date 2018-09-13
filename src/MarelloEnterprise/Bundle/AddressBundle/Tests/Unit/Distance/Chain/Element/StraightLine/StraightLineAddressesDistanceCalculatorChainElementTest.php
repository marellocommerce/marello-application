<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Distance\Chain\Element\StraightLine;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\StraightLine\StraightLineAddressesDistanceCalculatorChainElement;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\AddressBundle\Provider\AddressCoordinatesProviderInerface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\HttpFoundation\Session\Session;

class StraightLineAddressesDistanceCalculatorChainElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressCoordinatesProviderInerface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coordinatesProvider;

    /**
     * @var StraightLineAddressesDistanceCalculatorChainElement
     */
    protected $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->coordinatesProvider = $this->getMockBuilder(AddressCoordinatesProviderInerface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Session|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(Session::class);
        $this->distanceCalculator =
            new StraightLineAddressesDistanceCalculatorChainElement($this->coordinatesProvider, $session);
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param float $originLat
     * @param float $originLon
     * @param float $destinationLat
     * @param float $destinationLon
     * @param float $expectedDistance
     */
    public function testCalculate($originLat, $originLon, $destinationLat, $destinationLon, $expectedDistance)
    {
        /** @var FeatureChecker|\PHPUnit_Framework_MockObject_MockObject $featureChecker */
        $featureChecker = $this->getMockBuilder(FeatureChecker::class)->disableOriginalConstructor()->getMock();
        $featureChecker->expects(static::once())
            ->method('isFeatureEnabled')
            ->willReturn(true);

        $this->distanceCalculator->setFeatureChecker($featureChecker);

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
        $originAddress = new MarelloAddress();
        $destinationAddress = new MarelloAddress();

        $geocodedOriginAddress = new MarelloEnterpriseAddress();
        $geocodedOriginAddress
            ->setAddress($originAddress)
            ->setLatitude($originLat)
            ->setLongitude($originLon);

        $geocodedDestinationAddress = new MarelloEnterpriseAddress();
        $geocodedDestinationAddress
            ->setAddress($destinationAddress)
            ->setLatitude($destinationLat)
            ->setLongitude($destinationLon);

        static::assertEquals(
            $expectedDistance,
            $this->distanceCalculator->calculate($originAddress, $destinationAddress)
        );
    }

    /**
     * @return array
     */
    public function calculateDataProvider()
    {
        return [
            [
                'originLat' => 0,
                'originLon' => 0,
                'destinationLat' => 100,
                'destinationLon' => 100,
                'expectedDistance' => 9814.93
            ],
            [
                'originLat' => 10,
                'originLon' => 10,
                'destinationLat' => 200,
                'destinationLon' => 200,
                'expectedDistance' => 3510.68
            ]
        ];
    }
}
