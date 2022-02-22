<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Symfony\Contracts\Translation\TranslatorInterface;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Marello\Bundle\OrderBundle\Provider\DiscountSubtotalProvider;

class DiscountSubtotalProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translator;

    /**
     * @var RoundingServiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rounding;

    /**
     * @var DefaultCurrencyProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $defaultCurrencyProvider;

    /**
     * @var DiscountSubtotalProvider
     */
    protected $discountSubtotalProvider;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return $value;
                    }
                )
            );
        $this->rounding = $this->createMock(RoundingServiceInterface::class);
        $this->rounding->expects($this->any())
            ->method('round')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return round($value, 0, PHP_ROUND_HALF_UP);
                    }
                )
            );
        $this->defaultCurrencyProvider = $this->createMock(DefaultCurrencyProviderInterface::class);
        $this->discountSubtotalProvider = new DiscountSubtotalProvider(
            $this->translator,
            $this->rounding,
            $this->defaultCurrencyProvider
        );
    }

    public function testGetName()
    {
        static::assertEquals(DiscountSubtotalProvider::NAME, $this->discountSubtotalProvider->getName());
    }

    public function testGetSubtotal()
    {
        /** @var Order $entity */
        $entity = $this->getEntity(Order::class, ['id' => 1, 'currency' => 'USD', 'discountAmount' => 10.0]);

        $subtotal = $this->discountSubtotalProvider->getSubtotal($entity);
        $this->assertInstanceOf(Subtotal::class, $subtotal);
        $this->assertEquals(DiscountSubtotalProvider::TYPE, $subtotal->getType());
        $this->assertEquals('marello.order.subtotals.discount.label', $subtotal->getLabel());
        $this->assertEquals($entity->getCurrency(), $subtotal->getCurrency());
        $this->assertIsFloat($subtotal->getAmount());
        $this->assertEquals(10.0, $subtotal->getAmount());
    }

    public function testIsSupported()
    {
        static::assertTrue($this->discountSubtotalProvider->isSupported(new Order()));
        static::assertFalse($this->discountSubtotalProvider->isSupported(new OrderItem()));
    }
}
