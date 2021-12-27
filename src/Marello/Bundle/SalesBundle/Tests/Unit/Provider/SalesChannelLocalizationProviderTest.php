<?php

namespace Marello\Bundle\SalesBundle\Tests\Unit\Provider;

use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderAwareInterface;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Provider\SalesChannelLocalizationProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class SalesChannelLocalizationProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var SalesChannelLocalizationProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new SalesChannelLocalizationProvider();
    }

    public function testSalesChannelEntity()
    {
        $entity = $this->createMock(SalesChannel::class);
        $entity
            ->expects(static::once())
            ->method('getLocalization');

        $this->provider->getLocalization($entity);
    }
    
    public function testOrderEntity()
    {
        $salesChannel = $this->createMock(SalesChannel::class);
        $salesChannel
            ->expects(static::once())
            ->method('getLocalization');
        $entity = $this->createMock(Order::class);
        $entity
            ->expects(static::once())
            ->method('getSalesChannel')
        ->willReturn($salesChannel);

        $this->provider->getLocalization($entity);
    }

    public function testOrderAwareEntity()
    {
        $salesChannel = $this->createMock(SalesChannel::class);
        $salesChannel
            ->expects(static::once())
            ->method('getLocalization');
        $order = $this->createMock(Order::class);
        $order
            ->expects(static::once())
            ->method('getSalesChannel')
            ->willReturn($salesChannel);
        $entity = $this->createMock(Refund::class);
        $entity
            ->expects(static::once())
            ->method('getOrder')
            ->willReturn($order);
        $this->provider->getLocalization($entity);
    }
}
