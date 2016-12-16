<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Event;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;

class InventoryUpdateEventTest extends WebTestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryUpdateEvent $event */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp()
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
