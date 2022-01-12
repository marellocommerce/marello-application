<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Event\ResolveTaxEvent;
use Marello\Bundle\TaxBundle\Event\TaxEventDispatcher;

class TaxEventDispatcherTest extends TestCase
{
    public function testDispatch()
    {
        /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject $eventDispatcher */
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
