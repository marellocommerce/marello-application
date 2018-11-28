<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager;
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
     * @var BalancedInventoryManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $balancedInventoryManager;

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

        $this->balancedInventoryManager = $this
            ->getMockBuilder(BalancedInventoryManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new InventoryUpdateEventListener(
            $this->inventoryManager,
            $this->balancedInventoryManager
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
        $this->balancedInventoryManager->expects($this->once())
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
