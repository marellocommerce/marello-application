<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Provider;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Provider\BasicPaymentMethodChoicesProvider;
use Marello\Bundle\PaymentBundle\Tests\Unit\Provider\Stub\PaymentMethodStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasicPaymentMethodChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject phpdoc
     */
    protected $paymentMethodProvider;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject phpdoc
     */
    protected $translator;

    /**
     * @var BasicPaymentMethodChoicesProvider
     */
    protected $choicesProvider;

    protected function setUp(): void
    {
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->choicesProvider = new BasicPaymentMethodChoicesProvider(
            $this->paymentMethodProvider,
            $this->translator
        );
    }

    /**
     * @param array $methods
     * @param array $result
     * @param bool  $translate
     *
     * @dataProvider methodsProvider
     */
    public function testGetMethods($methods, $result, $translate = false)
    {
        $translation = [
            ['bank transfer', [], null, null, 'bank transfer translated'],
            ['payment term', [], null, null, 'payment term translated'],
        ];

        $this->paymentMethodProvider->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($methods);

        $this->translator->expects($this->any())
            ->method('trans')
            ->will($this->returnValueMap($translation));

        $this->assertEquals($result, $this->choicesProvider->getMethods($translate));
    }

    /**
     * @return array
     */
    public function methodsProvider()
    {
        return
            [
                'some_methods' =>
                    [
                        'methods' =>
                            [
                                'payment_term' => $this->getEntity(
                                    PaymentMethodStub::class,
                                    [
                                        'identifier' => 'payment_term',
                                        'sortOrder' => 1,
                                        'label' => 'payment term',
                                        'isEnabled' => false,
                                        'options' => [],
                                    ]
                                ),
                                'bank_transfer' => $this->getEntity(
                                    PaymentMethodStub::class,
                                    [
                                        'identifier' => 'bank_transfer',
                                        'sortOrder' => 1,
                                        'label' => 'bank transfer',
                                        'isEnabled' => true,
                                        'options' => [],
                                    ]
                                ),
                            ],
                        'result' => ['payment_term' => 'payment term', 'bank_transfer' => 'bank transfer'],
                        'translate' => false,
                    ],
                'some_methods_with_translation' =>
                    [
                        'methods' =>
                            [
                                'bank_transfer' => $this->getEntity(
                                    PaymentMethodStub::class,
                                    [
                                        'identifier' => 'bank_transfer',
                                        'sortOrder' => 1,
                                        'label' => 'bank transfer',
                                        'isEnabled' => true,
                                        'options' => [],
                                    ]
                                ),
                                'payment_term' => $this->getEntity(
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
                        'result' => ['bank_transfer' => 'bank transfer translated', 'payment_term' => 'payment term translated'],
                        'translate' => true,
                    ],
                'no_methods' =>
                    [
                        'methods' => [],
                        'result' => [],
                    ],
            ];
    }
}
