<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\TimeInTransit;

use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheableTimeInTransitProvider;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\Factory\TimeInTransitCacheProviderFactoryInterface;
use Marello\Bundle\UPSBundle\TimeInTransit\CacheProvider\TimeInTransitCacheProviderInterface;
use Marello\Bundle\UPSBundle\TimeInTransit\Result\TimeInTransitResultInterface;
use Marello\Bundle\UPSBundle\TimeInTransit\TimeInTransitProvider;
use Marello\Bundle\UPSBundle\TimeInTransit\TimeInTransitProviderInterface;
use Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs\AddressStub;

class CacheableTimeInTransitProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @internal
     */
    const PICKUP_DATE = '01.01.2018 12:00';

    /**
     * @internal
     */
    const TRANSPORT_ID = 1;

    /**
     * @var TimeInTransitResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timeInTransitResult;

    /**
     * @var UPSSettings|\PHPUnit_Framework_MockObject_MockObject
     */
    private $upsTransport;

    /**
     * @var TimeInTransitCacheProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timeInTransitCacheProvider;

    /**
     * @var TimeInTransitCacheProviderFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timeInTransitCacheProviderFactory;

    /**
     * @var TimeInTransitProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $timeInTransit;

    /**
     * @var CacheableTimeInTransitProvider
     */
    private $cacheableTimeInTransit;

    /**
     * @var \DateTime
     */
    private $pickupDate;

    /**
     * @var AddressStub
     */
    private $address;

    /**
     * @var int
     */
    private $weight;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->address = new AddressStub();
        $this->weight = 1;
        $this->pickupDate = \DateTime::createFromFormat('d.m.Y H:i', self::PICKUP_DATE);
        $this->upsTransport = $this->createMock(UPSSettings::class);
        $this->timeInTransitCacheProviderFactory = $this->createMock(TimeInTransitCacheProviderFactoryInterface::class);
        $this->timeInTransitCacheProvider = $this->createMock(TimeInTransitCacheProviderInterface::class);
        $this->timeInTransit = $this->createMock(TimeInTransitProvider::class);
        $this->timeInTransitResult = $this->createMock(TimeInTransitResultInterface::class);
        $this->cacheableTimeInTransit =
            new CacheableTimeInTransitProvider($this->timeInTransit, $this->timeInTransitCacheProviderFactory);
    }

    /**
     * @dataProvider timeInTransitResultStatusDataProvider
     *
     * @param bool $status
     * @param int  $saveCache
     */
    public function testGetTimeInTransitResult($status, $saveCache)
    {
        $this->mockTimeInTransitCacheProviderFactory();

        $this->timeInTransitCacheProvider
            ->expects(static::once())
            ->method('contains')
            ->with($this->address, $this->address, $this->pickupDate)
            ->willReturn(false);

        $this->timeInTransit
            ->expects(static::once())
            ->method('getTimeInTransitResult')
            ->with($this->upsTransport, $this->address, $this->address, $this->pickupDate)
            ->willReturn($this->timeInTransitResult);

        $this->timeInTransitResult
            ->expects(static::once())
            ->method('getStatus')
            ->willReturn($status);

        $this->timeInTransitCacheProvider
            ->expects(static::exactly($saveCache))
            ->method('save')
            ->with($this->address, $this->address, $this->pickupDate, $this->timeInTransitResult);

        $result = $this
            ->cacheableTimeInTransit
            ->getTimeInTransitResult(
                $this->upsTransport,
                $this->address,
                $this->address,
                $this->pickupDate,
                $this->weight
            );

        static::assertEquals($this->timeInTransitResult, $result);
    }

    /**
     * @return array
     */
    public function timeInTransitResultStatusDataProvider()
    {
        return [
            'result should be cached' => [
                'status' => true,
                'saveCache' => 1,
            ],
            'result should not be cached' => [
                'status' => false,
                'saveCache' => 0,
            ],
        ];
    }

    public function testGetTimeInTransitResultWhenCacheExists()
    {
        $this->mockTimeInTransitCacheProviderFactory();

        $this->timeInTransitCacheProvider
            ->expects(static::once())
            ->method('contains')
            ->with($this->address, $this->address, $this->pickupDate)
            ->willReturn(true);

        $this->timeInTransit
            ->expects(static::never())
            ->method('getTimeInTransitResult');

        $this->timeInTransitCacheProvider
            ->expects(static::once())
            ->method('fetch')
            ->with($this->address, $this->address, $this->pickupDate)
            ->willReturn($this->timeInTransitResult);

        $result = $this
            ->cacheableTimeInTransit
            ->getTimeInTransitResult(
                $this->upsTransport,
                $this->address,
                $this->address,
                $this->pickupDate,
                $this->weight
            );

        static::assertEquals($this->timeInTransitResult, $result);
    }

    private function mockTimeInTransitCacheProviderFactory()
    {
        $this->timeInTransitCacheProviderFactory
            ->expects(static::once())
            ->method('createCacheProviderForTransport')
            ->with($this->upsTransport)
            ->willReturn($this->timeInTransitCacheProvider);
    }
}
