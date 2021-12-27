<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Method\Event;

use Marello\Bundle\PaymentBundle\Method\Event\BasicMethodRemovalEventDispatcher;
use Marello\Bundle\PaymentBundle\Method\Event\MethodRemovalEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BasicMethodRemovalEventDispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    /**
     * @var BasicMethodRemovalEventDispatcher
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->dispatcher = new BasicMethodRemovalEventDispatcher($this->eventDispatcher);
    }

    public function testDispatch()
    {
        $methodId = 'method';
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(MethodRemovalEvent::NAME, new MethodRemovalEvent($methodId));

        $this->dispatcher->dispatch($methodId);
    }
}
