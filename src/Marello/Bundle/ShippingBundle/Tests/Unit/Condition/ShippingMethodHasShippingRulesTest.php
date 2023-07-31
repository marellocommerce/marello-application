<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Condition;

use Marello\Bundle\ShippingBundle\Condition\ShippingMethodHasShippingRules;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\ConfigExpression\ContextAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class ShippingMethodHasShippingRulesTest extends \PHPUnit\Framework\TestCase
{
    const PROPERTY_PATH_NAME = 'testPropertyPath';

    /**
     * @var ShippingMethodsConfigsRuleRepository|\PHPUnit\Framework\MockObject\MockObject
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
     * @var ShippingMethodHasShippingRules
     */
    protected $shippingMethodHasShippingRulesCondition;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ShippingMethodsConfigsRuleRepository::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->propertyPath = $this->createMock(PropertyPathInterface::class);
        $this->propertyPath->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::PROPERTY_PATH_NAME));
        $this->propertyPath->expects($this->any())
            ->method('getElements')
            ->will($this->returnValue([self::PROPERTY_PATH_NAME]));

        $this->shippingMethodHasShippingRulesCondition = new ShippingMethodHasShippingRules(
            $this->repository,
            $this->aclHelper
        );
    }

    public function testGetName()
    {
        $this->assertEquals(
            ShippingMethodHasShippingRules::NAME,
            $this->shippingMethodHasShippingRulesCondition->getName()
        );
    }

    public function testInitializeInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "method_identifier" option');
        $this->assertInstanceOf(
            ShippingMethodHasShippingRules::class,
            $this->shippingMethodHasShippingRulesCondition->initialize([])
        );
    }

    public function testInitialize()
    {
        $this->assertInstanceOf(
            ShippingMethodHasShippingRules::class,
            $this->shippingMethodHasShippingRulesCondition->initialize(['method_identifier'])
        );
    }

    /**
     * @dataProvider evaluateProvider
     *
     * @param ShippingMethodsConfigsRule[] $rules
     * @param bool                         $expected
     */
    public function testEvaluate($rules, $expected)
    {
        $this->repository->expects(static::once())
            ->method('getRulesByMethod')
            ->willReturn($rules);

        $this->shippingMethodHasShippingRulesCondition->initialize(['method_identifier']);
        $this->assertEquals($expected, $this->shippingMethodHasShippingRulesCondition->evaluate([]));
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
                    new ShippingMethodsConfigsRule(),
                    new ShippingMethodsConfigsRule(),
                ],
                'expected' => true,
            ],
        ];
    }

    public function testToArray()
    {
        $result = $this->shippingMethodHasShippingRulesCondition->initialize([$this->propertyPath])->toArray();

        $this->assertEquals(
            sprintf('$%s', self::PROPERTY_PATH_NAME),
            $result['@marello_shipping_method_has_shipping_rules']['parameters'][0]
        );
    }

    public function testCompile()
    {
        $result = $this->shippingMethodHasShippingRulesCondition->compile('$factoryAccessor');

        $this->assertStringContainsString('$factoryAccessor->create(\'marello_shipping_method_has_shipping_rules\'', $result);
    }

    public function testSetContextAccessor()
    {
        /** @var ContextAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $contextAccessor * */
        $contextAccessor = $this->getMockBuilder(ContextAccessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shippingMethodHasShippingRulesCondition->setContextAccessor($contextAccessor);

        $reflection = new \ReflectionProperty(
            get_class($this->shippingMethodHasShippingRulesCondition),
            'contextAccessor'
        );

        $this->assertInstanceOf(
            get_class($contextAccessor),
            $reflection->getValue($this->shippingMethodHasShippingRulesCondition)
        );
    }
}
