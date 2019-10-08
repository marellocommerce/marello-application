<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Method\Event;

use Marello\Bundle\ShippingBundle\Method\Event\MethodTypeRemovalEvent;

class MethodTypeRemovalEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters()
    {
        $methodId = 'method';
        $typeId = 'type';

        $event = new MethodTypeRemovalEvent($methodId, $typeId);

        $this->assertSame($methodId, $event->getMethodIdentifier());
        $this->assertSame($typeId, $event->getTypeIdentifier());
    }
}
