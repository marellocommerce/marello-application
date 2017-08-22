<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\EventListener;

use Marello\Bundle\TaxBundle\Event\ResolveTaxEvent;
use Marello\Bundle\TaxBundle\Event\TaxEventDispatcher;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaxEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $taxDispatcher = new TaxEventDispatcher($eventDispatcher);
        $taxable = new Taxable();

        $eventDispatcher->expects($this->exactly(3))->method('dispatch')
            ->withConsecutive(
                [ResolveTaxEvent::RESOLVE_BEFORE, $this->isInstanceOf(ResolveTaxEvent::class)],
                [ResolveTaxEvent::RESOLVE, $this->isInstanceOf(ResolveTaxEvent::class)],
                [ResolveTaxEvent::RESOLVE_AFTER, $this->isInstanceOf(ResolveTaxEvent::class)]
            );

        $taxDispatcher->dispatch($taxable);
    }
}
