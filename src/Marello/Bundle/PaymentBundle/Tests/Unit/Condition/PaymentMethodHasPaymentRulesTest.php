<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Condition;

use Marello\Bundle\PaymentBundle\Condition\PaymentMethodHasPaymentRules;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\ConfigExpression\ContextAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class PaymentMethodHasPaymentRulesTest extends \PHPUnit\Framework\TestCase
{
    const PROPERTY_PATH_NAME = 'testPropertyPath';

    /**
     * @var PaymentMethodsConfigsRuleRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $repository;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var PropertyPathInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $propertyPath;

    /**
     * @var PaymentMethodHasPaymentRules
     */
    protected $paymentMethodHasPaymentRulesCondition;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PaymentMethodsConfigsRuleRepository::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->propertyPath = $this->createMock(PropertyPathInterface::class);
        $this->propertyPath->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::PROPERTY_PATH_NAME));
        $this->propertyPath->expects($this->any())
            ->method('getElements')
            ->will($this->returnValue([self::PROPERTY_PATH_NAME]));

        $this->paymentMethodHasPaymentRulesCondition = new PaymentMethodHasPaymentRules(
            $this->repository,
            $this->aclHelper
        );
    }

    public function testGetName()
    {
        $this->assertEquals(
            PaymentMethodHasPaymentRules::NAME,
            $this->paymentMethodHasPaymentRulesCondition->getName()
        );
    }

    public function testInitializeInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "method_identifier" option');
        $this->assertInstanceOf(
            PaymentMethodHasPaymentRules::class,
            $this->paymentMethodHasPaymentRulesCondition->initialize([])
        );
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            PaymentMethodHasPaymentRules::class,
            $this->paymentMethodHasPaymentRulesCondition->initialize(['method_identifier'])
        );
    }

    /**
     * @dataProvider evaluateProvider
     *
     * @param PaymentMethodsConfigsRule[] $rules
     * @param bool                         $expected
     */
    public function testEvaluate($rules, $expected)
    {
        $this->repository->expects(static::once())
            ->method('getRulesByMethod')
            ->willReturn($rules);

        $this->paymentMethodHasPaymentRulesCondition->initialize(['method_identifier']);
        $this->assertEquals($expected, $this->paymentMethodHasPaymentRulesCondition->evaluate([]));
    }

    /**
     * @return array
     */
    public function evaluateProvider()
    {
        return [
            'no_rules' => [
                'rules' => [],
                'expected' => false,
            ],
            'with_rules' => [
                'rules' => [
                    new PaymentMethodsConfigsRule(),
                    new PaymentMethodsConfigsRule(),
                ],
                'expected' => true,
            ],
        ];
    }

    public function testToArray()
    {
        $result = $this->paymentMethodHasPaymentRulesCondition->initialize([$this->propertyPath])->toArray();

        $this->assertEquals(
            sprintf('$%s', self::PROPERTY_PATH_NAME),
            $result['@marello_payment_method_has_payment_rules']['parameters'][0]
        );
    }

    public function testCompile()
    {
        $result = $this->paymentMethodHasPaymentRulesCondition->compile('$factoryAccessor');

        $this->assertStringContainsString('$factoryAccessor->create(\'marello_payment_method_has_payment_rules\'', $result);
    }

    public function testSetContextAccessor()
    {
        /** @var ContextAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $contextAccessor * */
        $contextAccessor = $this->getMockBuilder(ContextAccessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethodHasPaymentRulesCondition->setContextAccessor($contextAccessor);

        $reflection = new \ReflectionProperty(
            get_class($this->paymentMethodHasPaymentRulesCondition),
            'contextAccessor'
        );

        $this->assertInstanceOf(
            get_class($contextAccessor),
            $reflection->getValue($this->paymentMethodHasPaymentRulesCondition)
        );
    }
}
