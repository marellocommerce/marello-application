<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderAwareInterface;
use Marello\Bundle\OrderBundle\Provider\OrderLocalizationProvider;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class OrderLocalizationProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var OrderLocalizationProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new OrderLocalizationProvider();
    }
    
    public function testOrderEntity()
    {
        $order = $this->createMock(Order::class);
        $order
            ->expects(static::once())
            ->method('getLocalization');

        $this->provider->getLocalization($order);
    }

    public function testOrderAwareEntity()
    {
        $order = $this->createMock(Order::class);
        $order
            ->expects(static::once())
            ->method('getLocalization');
        $entity = $this->createMock(Refund::class);
        $entity
            ->expects(static::once())
            ->method('getOrder')
            ->willReturn($order);
        $this->provider->getLocalization($entity);
    }
}
