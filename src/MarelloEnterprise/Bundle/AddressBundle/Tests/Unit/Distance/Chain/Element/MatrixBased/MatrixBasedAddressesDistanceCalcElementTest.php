<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Tests\Unit\Distance\Chain\Element\MatrixBased;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\AddressesDistanceCalculatorInterface;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\MatrixBased\MatrixBasedAddressesDistanceCalcElement;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\DistanceMatrixApiResultFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResult;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\GoogleApiResultInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class MatrixBasedAddressesDistanceCalcElementTest extends TestCase
{
    /**
     * @var GoogleApiResultsProviderInterface|MockObject
     */
    protected $distanceMatrixResultsProvider;

    /**
     * @var MatrixBasedAddressesDistanceCalcElement
     */
    protected $distanceCalculator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->distanceMatrixResultsProvider = $this->createMock(GoogleApiResultsProviderInterface::class);
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var Session|MockObject $session */
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
     * @param AddressesDistanceCalculatorInterface|MockObject $successor
     */
    public function testCalculate(
        $isFeatureEnabled,
        GoogleApiResultInterface $apiResults,
        $expectedDistance,
        MockObject $successor = null
    ) {
        /** @var FeatureChecker|MockObject $featureChecker */
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
