<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Checker;

use Marello\Bundle\PaymentBundle\Checker\PaymentMethodEnabledByIdentifierChecker;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class PaymentMethodEnabledByIdentifierCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $method;

    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shippingMethodProvider;

    /**
     * @var PaymentMethodEnabledByIdentifierChecker
     */
    protected $shippingMethodEnabledByIdentifierChecker;

    protected function setUp(): void
    {
        $this->method = $this->createMock(PaymentMethodInterface::class);

        $this->shippingMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);

        $this->shippingMethodEnabledByIdentifierChecker = new PaymentMethodEnabledByIdentifierChecker(
            $this->shippingMethodProvider
        );
    }

    public function testIsEnabledForEnabledMethod()
    {
        $identifier = 'shipping_method_1';

        $this->method
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->shippingMethodProvider
            ->expects(static::any())
            ->method('getPaymentMethod')
            ->with($identifier)
            ->willReturn($this->method);

        $this->assertTrue($this->shippingMethodEnabledByIdentifierChecker->isEnabled($identifier));
    }

    public function testIsEnabledForDisabledMethod()
    {
        $identifier = 'shipping_method_1';

        $this->method
            ->expects(static::once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->shippingMethodProvider
            ->expects(static::any())
            ->method('getPaymentMethod')
            ->with($identifier)
            ->willReturn($this->method);

        $this->assertFalse($this->shippingMethodEnabledByIdentifierChecker->isEnabled($identifier));
    }

    public function testIsEnabledForNotExistingMethod()
    {
        $identifier = 'shipping_method_1';

        $this->shippingMethodProvider
            ->expects(static::any())
            ->method('getPaymentMethod')
            ->with($identifier)
            ->willReturn(null);

        $this->assertFalse($this->shippingMethodEnabledByIdentifierChecker->isEnabled($identifier));
    }
}
