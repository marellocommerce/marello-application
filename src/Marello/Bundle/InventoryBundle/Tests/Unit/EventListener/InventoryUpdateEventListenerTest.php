<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Marello\Bundle\InventoryBundle\Manager\VirtualInventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;

class InventoryUpdateEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InventoryUpdateContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryUpdateContext;

    /**
     * @var InventoryUpdateEventListener
     */
    protected $listener;

    /**
     * @var InventoryManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryManager;

    /**
     * @var VirtualInventoryManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $virtualInventoryManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->inventoryUpdateContext = new InventoryUpdateContext();
        $this->inventoryManager = $this
            ->getMockBuilder(InventoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->virtualInventoryManager = $this
            ->getMockBuilder(VirtualInventoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new InventoryUpdateEventListener(
            $this->inventoryManager,
            $this->virtualInventoryManager
        );
    }

    public function testHandleUpdateInventoryEvent()
    {
        $event = $this->prepareEvent();
        $this->inventoryManager->expects($this->once())
            ->method('updateInventoryLevel')
            ->with($this->inventoryUpdateContext);

        $this->listener->handleUpdateInventoryEvent($event);
    }

    public function testHandleEventWithVirtualInventoryContext()
    {
        $this->inventoryUpdateContext->setIsVirtual(true);
        $event = $this->prepareEvent();
        $this->virtualInventoryManager->expects($this->once())
            ->method('updateInventoryLevel')
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
