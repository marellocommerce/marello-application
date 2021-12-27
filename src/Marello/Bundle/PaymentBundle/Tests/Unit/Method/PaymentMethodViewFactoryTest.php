<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodViewFactory;

class PaymentMethodViewFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodProviderMock;

    /**
     * @var PaymentMethodViewFactory
     */
    private $paymentMethodViewFactory;

    public function setUp(): void
    {
        $this->paymentMethodProviderMock = $this
            ->getMockBuilder(PaymentMethodProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethodViewFactory = new PaymentMethodViewFactory($this->paymentMethodProviderMock);
    }

    public function testCreateMethodView()
    {
        $methodId = 'someId';
        $label = 'someLabel';
        $sortOrder = 5;
        $options = [
            'option1',
            'option2'
        ];

        $expected = [
            'identifier' => $methodId,
            'label' => $label,
            'sortOrder' => $sortOrder,
            'options' => $options
        ];

        $actual = $this->paymentMethodViewFactory->createMethodView($methodId, $label, $sortOrder, $options);

        $this->assertEquals($expected, $actual);
    }
}
