<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;

/**
 * @method \PHPUnit\Framework\MockObject\MockObject createMock(string $originalClassName)
 */
trait ShippingContextMockTrait
{
    /**
     * @return mixed
     */
    private function createShippingContextMock()
    {
        return $this->createMock(ShippingContextInterface::class);
    }
}
