<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Provider\DiscountSubtotalProvider;
use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Translation\TranslatorInterface;

class DiscountSubtotalProviderTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var RoundingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rounding;

    /**
     * @var DefaultCurrencyProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultCurrencyProvider;

    /**
     * @var DiscountSubtotalProvider
     */
    protected $discountSubtotalProvider;

    protected function setUp()
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
        $this->assertInternalType('float', $subtotal->getAmount());
        $this->assertEquals(10.0, $subtotal->getAmount());
    }

    public function testIsSupported()
    {
        static::assertTrue($this->discountSubtotalProvider->isSupported(new Order()));
        static::assertFalse($this->discountSubtotalProvider->isSupported(new OrderItem()));
    }
}
