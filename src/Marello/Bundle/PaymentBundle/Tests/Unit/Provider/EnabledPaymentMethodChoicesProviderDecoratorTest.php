<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Provider;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Provider\EnabledPaymentMethodChoicesProviderDecorator;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodChoicesProviderInterface;
use Marello\Bundle\PaymentBundle\Tests\Unit\Provider\Stub\PaymentMethodStub;
use Oro\Component\Testing\Unit\EntityTrait;

class EnabledPaymentMethodChoicesProviderDecoratorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject phpdoc
     */
    protected $paymentMethodProvider;

    /**
     * @var PaymentMethodChoicesProviderInterface|\PHPUnit\Framework\MockObject\MockObject phpdoc
     */
    protected $choicesProvider;

    /**
     * @var PaymentMethodChoicesProviderInterface
     */
    protected $enabledChoicesProvider;

    protected function setUp(): void
    {
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $this->choicesProvider = $this->createMock(PaymentMethodChoicesProviderInterface::class);
        $this->enabledChoicesProvider = new EnabledPaymentMethodChoicesProviderDecorator(
            $this->paymentMethodProvider,
            $this->choicesProvider
        );
    }

    /**
     * @param array $registryMap
     * @param array $choices
     * @param array $result
     *
     * @dataProvider methodsProvider
     */
    public function testGetMethods($registryMap, $choices, $result)
    {
        $this->paymentMethodProvider->expects($this->any())
            ->method('getPaymentMethod')
            ->will($this->returnValueMap($registryMap));

        $this->choicesProvider->expects($this->once())
            ->method('getMethods')
            ->willReturn($choices);

        $this->assertEquals($result, $this->enabledChoicesProvider->getMethods());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function methodsProvider()
    {
        return
            [
                'all_methods_enabled' =>
                    [
                        'methods_map' =>
                            [
                                [
                                    'bank_transfer',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'bank_transfer',
                                            'sortOrder' => 1,
                                            'label' => 'bank transfer',
                                            'isEnabled' => true,
                                            'options' => ['instructions'],
                                        ]
                                    ),
                                ],
                                [
                                    'payment_term',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'payment_term',
                                            'sortOrder' => 1,
                                            'label' => 'payment term',
                                            'isEnabled' => true,
                                            'options' => [],
                                        ]
                                    ),
                                ],
                            ],
                        'choices' => ['payment_term' => 'payment term', 'bank_transfer' => 'bank transfer'],
                        'result' => ['payment_term' => 'payment term', 'bank_transfer' => 'bank transfer'],
                    ],
                'some_methods_disabled' =>
                    [
                        'methods_map' =>
                            [
                                [
                                    'bank_transfer',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'bank_transfer',
                                            'sortOrder' => 1,
                                            'label' => 'bank transfer',
                                            'isEnabled' => true,
                                            'options' => ['instructions'],
                                        ]
                                    ),
                                ],
                                [
                                    'payment_term',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'payment_term',
                                            'sortOrder' => 1,
                                            'label' => 'payment term',
                                            'isEnabled' => false,
                                            'options' => [],
                                        ]
                                    ),
                                ],
                            ],
                        'choices' => ['bank_transfer' => 'bank transfer', 'payment_term' => 'payment term'],
                        'result' => ['bank_transfer' => 'bank transfer'],
                    ],
                'all_disabled_methods' =>
                    [
                        'methods_map' =>
                            [
                                [
                                    'bank_transfer',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'bank_transfer',
                                            'sortOrder' => 1,
                                            'label' => 'bank transfer',
                                            'isEnabled' => false,
                                            'options' => [],
                                        ]
                                    ),
                                ],
                                [
                                    'payment_term',
                                    $this->getEntity(
                                        PaymentMethodStub::class,
                                        [
                                            'identifier' => 'payment_term',
                                            'sortOrder' => 1,
                                            'label' => 'payment term',
                                            'isEnabled' => false,
                                            'options' => [],
                                        ]
                                    ),
                                ],
                            ],
                        'choices' => ['flat rate' => 'flat_rate', 'ups' => 'ups'],
                        'result' => [],
                    ],
                'no_methods' =>
                    [
                        'methods' => [],
                        'choices' => [],
                        'result' => [],
                    ],
            ];
    }
}
