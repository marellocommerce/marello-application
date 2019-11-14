<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;

/**
 * @method \PHPUnit\Framework\MockObject\MockObject createMock(string $originalClassName)
 */
trait PaymentContextMockTrait
{
    /**
     * @return mixed
     */
    private function createPaymentContextMock()
    {
        return $this->createMock(PaymentContextInterface::class);
    }
}
