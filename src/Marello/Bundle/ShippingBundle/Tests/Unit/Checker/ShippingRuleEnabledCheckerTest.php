<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Checker;

use Marello\Bundle\ShippingBundle\Checker\ShippingMethodEnabledByIdentifierCheckerInterface;
use Marello\Bundle\ShippingBundle\Checker\ShippingRuleEnabledChecker;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

class ShippingRuleEnabledCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingMethodEnabledByIdentifierCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $methodEnabledChecker;

    /**
     * @var ShippingRuleEnabledChecker
     */
    private $ruleChecker;

    protected function setUp(): void
    {
        $this->methodEnabledChecker = $this->createMock(
            ShippingMethodEnabledByIdentifierCheckerInterface::class
        );

        $this->ruleChecker = new ShippingRuleEnabledChecker($this->methodEnabledChecker);
    }

    public function testCanBeEnabledForOneEnabledMethod()
    {
        $this->methodEnabledChecker->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);

        $rule = $this->getRuleMock();

        static::assertTrue($this->ruleChecker->canBeEnabled($rule));
    }

    public function testCanBeEnabledForNoEnabledMethods()
    {
        $rule = $this->getRuleMock();

        static::assertFalse($this->ruleChecker->canBeEnabled($rule));
    }

    /**
     * @return ShippingMethodsConfigsRule|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRuleMock()
    {
        $rule = $this->createMock(ShippingMethodsConfigsRule::class);
        $rule->expects(static::any())
            ->method('getMethodConfigs')
            ->willReturn([
                new ShippingMethodConfig(), new ShippingMethodConfig()
            ]);

        return $rule;
    }
}
