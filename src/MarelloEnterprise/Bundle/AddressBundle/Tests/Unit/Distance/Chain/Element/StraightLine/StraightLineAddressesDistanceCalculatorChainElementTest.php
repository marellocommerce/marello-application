<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Distance\Chain\Element\StraightLine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\StraightLine\
StraightLineAddressesDistanceCalculatorChainElement;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class StraightLineAddressesDistanceCalculatorChainElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var StraightLineAddressesDistanceCalculatorChainElement
     */
    protected $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->distanceCalculator =
            new StraightLineAddressesDistanceCalculatorChainElement($this->doctrineHelper);
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
        $repository = $this->createMock(EntityRepository::class);
        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper
            ->expects(static::once())
            ->method('getEntityManagerForClass')
            ->with(MarelloEnterpriseAddress::class)
            ->willReturn($em);
        $em
            ->expects(static::once())
            ->method('getRepository')
            ->with(MarelloEnterpriseAddress::class)
            ->willReturn($repository);

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
        
        $repository->expects(static::exactly(2))
            ->method('findOneBy')
            ->withConsecutive(
                [['address' => $originAddress]],
                [['address' => $destinationAddress]]
            )
            ->willReturnOnConsecutiveCalls(
                $geocodedOriginAddress,
                $geocodedDestinationAddress
            );

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
