<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Checker;

use Marello\Bundle\PaymentBundle\Checker\PaymentMethodEnabledByIdentifierCheckerInterface;
use Marello\Bundle\PaymentBundle\Checker\PaymentRuleEnabledChecker;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

class PaymentRuleEnabledCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodEnabledByIdentifierCheckerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $methodEnabledChecker;

    /**
     * @var PaymentRuleEnabledChecker
     */
    private $ruleChecker;

    protected function setUp(): void
    {
        $this->methodEnabledChecker = $this->createMock(
            PaymentMethodEnabledByIdentifierCheckerInterface::class
        );

        $this->ruleChecker = new PaymentRuleEnabledChecker($this->methodEnabledChecker);
    }

    public function testCanBeEnabledForOneEnabledMethod()
    {
        $this->methodEnabledChecker->expects(static::any())
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
     * @return PaymentMethodsConfigsRule|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRuleMock()
    {
        $rule = $this->createMock(PaymentMethodsConfigsRule::class);
        $rule->expects(static::any())
            ->method('getMethodConfigs')
            ->willReturn([
                new PaymentMethodConfig(), new PaymentMethodConfig()
            ]);

        return $rule;
    }
}
