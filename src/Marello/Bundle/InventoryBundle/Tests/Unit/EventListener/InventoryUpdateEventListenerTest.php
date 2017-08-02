<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;

class InventoryUpdateEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryUpdateEventListener $listener */
    protected $listener;

    /** @var InventoryManager $inventoryManager */
    protected $inventoryManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->inventoryUpdateContext = $this->createMock(InventoryUpdateContext::class);
        $this->inventoryManager = $this
            ->getMockBuilder(InventoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new InventoryUpdateEventListener($this->inventoryManager);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandleUpdateInventoryEvent()
    {
        $event = $this->prepareEvent();
        $this->inventoryManager->expects($this->once())
            ->method('updateInventoryLevels')
            ->with($this->inventoryUpdateContext);

        $this->listener->handleUpdateInventoryEvent($event);
    }

    /**
     * @return InventoryUpdateEvent
     */
    protected function prepareEvent()
    {
        return new InventoryUpdateEvent($this->inventoryUpdateContext);
    }
}
