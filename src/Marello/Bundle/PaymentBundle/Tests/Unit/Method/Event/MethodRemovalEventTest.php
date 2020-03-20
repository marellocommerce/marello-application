<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method\Event;

use Marello\Bundle\PaymentBundle\Method\Event\MethodRemovalEvent;

class MethodRemovalEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $methodId = 'method';

        $event = new MethodRemovalEvent($methodId);

        $this->assertSame($methodId, $event->getMethodIdentifier());
    }
}
