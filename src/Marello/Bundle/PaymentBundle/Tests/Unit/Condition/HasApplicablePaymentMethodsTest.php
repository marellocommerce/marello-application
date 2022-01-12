<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Condition;

use Marello\Bundle\PaymentBundle\Condition\HasApplicablePaymentMethods;
use Marello\Bundle\PaymentBundle\Context\PaymentContext;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Provider\PaymentMethodsViewsProviderInterface;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Oro\Component\Testing\Unit\EntityTrait;

class HasApplicablePaymentMethodsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const METHOD = 'Method';

    /** @var HasApplicablePaymentMethods */
    protected $condition;

    /** @var PaymentMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentMethodProvider;

    /** @var PaymentMethodsViewsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentMethodsViewsProvider;


    protected function setUp(): void
    {
        $this->paymentMethodProvider = $this->createMock(PaymentMethodProviderInterface::class);

        $this->paymentMethodsViewsProvider = $this
            ->getMockBuilder(PaymentMethodsViewsProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->condition = new HasApplicablePaymentMethods(
            $this->paymentMethodProvider,
            $this->paymentMethodsViewsProvider
        );
    }

    protected function tearDown(): void
    {
        unset($this->condition, $this->paymentMethodProvider);
    }

    public function testGetName()
    {
        $this->assertEquals(HasApplicablePaymentMethods::NAME, $this->condition->getName());
    }

    public function testInitializeInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "paymentContext" option');
        $this->assertInstanceOf(
            'Oro\Component\ConfigExpression\Condition\AbstractCondition',
            $this->condition->initialize([])
        );
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            'Oro\Component\ConfigExpression\Condition\AbstractCondition',
            $this->condition->initialize([self::METHOD, new \stdClass()])
        );
    }

    /**
     * @dataProvider evaluateProvider
     * @param array $methods
     * @param bool $expected
     */
    public function testEvaluate($methods, $expected)
    {
        $method = $this->createMock('Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface');
        $this->paymentMethodProvider->expects($this->any())->method('getPaymentMethod')->willReturn($method);

        $this->paymentMethodsViewsProvider->expects($this->once())
            ->method('getApplicableMethodsViews')
            ->willReturn($methods);

        $this->condition->initialize(['paymentContext' => new PaymentContext([])]);
        $this->assertEquals($expected, $this->condition->evaluate([]));
    }

    /**
     * @return array
     */
    public function evaluateProvider()
    {
        return [
            'no_rules_no_methods' => [
                'methods' => [],
                'expected' => false,
            ],
            'with_rules_no_methods' => [
                'methods' => [],
                'expected' => false,
            ],
            'with_rules_and_methods' => [
                'methods' => ['flat_rate'],
                'expected' => true,
            ],
        ];
    }

    public function testToArray()
    {
        $stdClass = new \stdClass();
        $this->condition->initialize(['paymentContext' => $stdClass]);
        $result = $this->condition->toArray();

        $key = '@' . HasApplicablePaymentMethods::NAME;

        $this->assertIsArray($result);
        $this->assertArrayHasKey($key, $result);

        $resultSection = $result[$key];
        $this->assertIsArray($resultSection);
        $this->assertArrayHasKey('parameters', $resultSection);
        $this->assertContains($stdClass, $resultSection['parameters']);
    }

    public function testCompile()
    {
        $toStringStub = new ToStringStub();
        $options = ['paymentContext' => $toStringStub];

        $this->condition->initialize($options);
        $result = $this->condition->compile('$factory');
        $this->assertEquals(
            sprintf(
                '$factory->create(\'%s\', [%s])',
                HasApplicablePaymentMethods::NAME,
                $toStringStub
            ),
            $result
        );
    }
}
