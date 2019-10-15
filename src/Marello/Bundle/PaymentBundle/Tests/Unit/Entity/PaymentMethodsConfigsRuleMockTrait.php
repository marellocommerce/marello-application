<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Entity;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

/**
 * @method \PHPUnit\Framework\MockObject\MockObject createMock(string $originalClassName)
 */
trait PaymentMethodsConfigsRuleMockTrait
{
    /**
     * @return PaymentMethodsConfigsRule|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPaymentMethodsConfigsRuleMock()
    {
        return $this->createMock(PaymentMethodsConfigsRule::class);
    }
}
