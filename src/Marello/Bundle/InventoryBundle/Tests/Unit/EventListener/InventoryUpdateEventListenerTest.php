<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\EventListener\InventoryUpdateEventListener;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerManager;

class InventoryUpdateEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryUpdateEventListener $listener */
    protected $listener;

    /** @var InventoryBalancerManager $inventoryBalancerManager */
    protected $inventoryBalancerManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->inventoryUpdateContext = $this->getMock(InventoryUpdateContext::class);
        $this->inventoryBalancerManager = $this
            ->getMockBuilder(InventoryBalancerManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new InventoryUpdateEventListener($this->inventoryBalancerManager);
    }

    /**
     * {@inheritdoc}
     */
    public function testHandleUpdateInventoryEvent()
    {
        $event = $this->prepareEvent();
        $this->inventoryBalancerManager->expects($this->once())
            ->method('balanceInventory')
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
