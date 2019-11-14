<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Method\Event;

use Marello\Bundle\ShippingBundle\Method\Event\MethodRemovalEvent;

class MethodRemovalEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $methodId = 'method';

        $event = new MethodRemovalEvent($methodId);

        $this->assertSame($methodId, $event->getMethodIdentifier());
    }
}
