<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryUpdateEventTest extends TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryUpdateEvent $event */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->inventoryUpdateContext = $this
            ->getMockBuilder(InventoryUpdateContext::class)
            ->getMock();

        $this->event = new InventoryUpdateEvent($this->inventoryUpdateContext);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetContextFromEvent()
    {
        $this->assertEquals($this->inventoryUpdateContext, $this->event->getInventoryUpdateContext());
    }
}
