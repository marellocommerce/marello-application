<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method;

use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\CompositePaymentMethodProvider;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class CompositePaymentMethodProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompositePaymentMethodProvider
     */
    protected $paymentMethodProvider;

    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $provider;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->paymentMethodProvider = new CompositePaymentMethodProvider();

        $this->provider = $this->createMock(PaymentMethodProviderInterface::class);
    }

    public function testGetMethods()
    {
        $paymentMethods = $this->paymentMethodProvider->getPaymentMethods();
        $this->assertIsArray($paymentMethods);
        $this->assertEmpty($paymentMethods);
    }

    public function testRegistry()
    {
        $method = $this->createMock(PaymentMethodInterface::class);

        $this->provider->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn(['test_name' => $method]);

        $this->provider->expects($this->once())
            ->method('getPaymentMethod')
            ->with('test_name')
            ->willReturn($method);

        $this->provider->expects($this->once())
            ->method('hasPaymentMethod')
            ->with('test_name')
            ->willReturn(true);

        $this->paymentMethodProvider->addProvider($this->provider);
        $this->assertEquals($method, $this->paymentMethodProvider->getPaymentMethod('test_name'));
        $this->assertEquals(['test_name' => $method], $this->paymentMethodProvider->getPaymentMethods());
    }
}
