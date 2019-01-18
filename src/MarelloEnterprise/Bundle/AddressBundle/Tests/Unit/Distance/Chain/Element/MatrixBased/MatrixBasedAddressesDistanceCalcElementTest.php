<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Distance\Chain\Element\MatrixBased;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Session\Session;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\DistanceMatrixApiResultFactory;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\MatrixBased\MatrixBasedAddressesDistanceCalcElement;

class MatrixBasedAddressesDistanceCalcElementTest extends TestCase
{
    /**
     * @var GoogleApiResultsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $distanceMatrixResultsProvider;

    /**
     * @var MatrixBasedAddressesDistanceCalcElement
     */
    protected $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->distanceMatrixResultsProvider = $this->createMock(GoogleApiResultsProviderInterface::class);
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var Session|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(Session::class);
        $this->distanceCalculator =
            new MatrixBasedAddressesDistanceCalcElement(
                $this->distanceMatrixResultsProvider,
                $logger,
                $session
            );
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param bool $isFeatureEnabled
     * @param GoogleApiResultInterface $apiResults
     * @param float $expectedDistance
     * @param AddressesDistanceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject $successor
     */
    public function testCalculate(
        $isFeatureEnabled,
        GoogleApiResultInterface $apiResults,
        $expectedDistance,
        \PHPUnit_Framework_MockObject_MockObject $successor = null
    ) {
        /** @var FeatureChecker|\PHPUnit_Framework_MockObject_MockObject $featureChecker */
        $featureChecker = $this->getMockBuilder(FeatureChecker::class)->disableOriginalConstructor()->getMock();
        $featureChecker->expects(static::once())
            ->method('isFeatureEnabled')
            ->willReturn($isFeatureEnabled);

        $this->distanceCalculator->setFeatureChecker($featureChecker);

        if ($successor) {
            $this->distanceCalculator->setSuccessor($successor);
        }

        $this->distanceMatrixResultsProvider
            ->expects(static::any())
            ->method('getApiResults')
            ->willReturn($apiResults);

        $originAddress = new MarelloAddress();
        $destinationAddress = new MarelloAddress();

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
        $successor = $this->createMock(AddressesDistanceCalculatorInterface::class);
        $successor->expects(static::once())
            ->method('calculate')
            ->willReturn(50);

        return [
            [
                'isFeatureEnabled' => true,
                'apiResults' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        DistanceMatrixApiResultFactory::DISTANCE => 10000
                    ]
                ]),
                'expectedDistance' => 10,
                'successor' =>null
            ],
            [
                'isFeatureEnabled' => false,
                'apiResults' => new GoogleApiResult([
                    GoogleApiResult::FIELD_STATUS => true,
                    GoogleApiResult::FIELD_RESULT => [
                        DistanceMatrixApiResultFactory::DISTANCE => 50000
                    ]
                ]),
                'expectedDistance' => 50,
                'successor' => $successor
            ]
        ];
    }
}
