<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\VirtualInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Entity\Repository\VirtualInventoryRepository;
use Marello\Bundle\InventoryBundle\EventListener\VirtualInventoryLevelUpdateAfterEventListener;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class VirtualInventoryLevelUpdateAfterEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InventoryUpdateContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryUpdateContext;

    /**
     * @var VirtualInventoryLevelUpdateAfterEventListener $listener
     */
    protected $listener;

    /**
     * @var InventoryManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inventoryManager;

    /**
     * @var MessageProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $producer;

    /**
     * @var VirtualInventoryRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->inventoryManager = $this->createMock(InventoryManager::class);

        $this->producer = $this->createMock(MessageProducerInterface::class);
        $configManager =  $this->createMock(ConfigManager::class);
        $calculator = new InventoryBalancerTriggerCalculator($configManager);

        $this->repository =  $this->createMock(VirtualInventoryRepository::class);

        $this->listener = new VirtualInventoryLevelUpdateAfterEventListener(
            $this->producer,
            $calculator,
            $this->repository
        );
    }

    /**
     * Test that the event is not handled because the context is for a virtual inventory level
     */
    public function testEventIsNotHandledDuringWrongContext()
    {
        $context = new InventoryUpdateContext();
        $event = $this->prepareEvent($context);

        $result = $this->listener->handleInventoryUpdateAfterEvent($event);
        $this->assertNull($result);
    }

    /**
     * Test that the event is not handled because the context is for an inventory level
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage To few arguments given in the context, no virtualInventoryLevel or salesChannelGroup given, please check your data
     */
    public function testThrowInvalidArgumentExceptionOnToFewDataGivenInContext()
    {
        $context = new InventoryUpdateContext();
        $context->setIsVirtual(true);
        $event = $this->prepareEvent($context);

        $this->listener->handleInventoryUpdateAfterEvent($event);
    }

    public function testRebalanceThresholdHasBeenReachedAndTriggerIsBeingSend()
    {
        $context = new InventoryUpdateContext();
        $context->setValue('virtualInventoryLevel', $this->createMock(InventoryLevel::class));
        $context->setValue('salesChannelGroup', $this->createMock(SalesChannelGroup::class));
        $context->setProduct($this->createMock(Product::class));
        $context->setIsVirtual(true);
        $event = $this->prepareEvent($context);

        /** @var InventoryBalancerTriggerCalculator|\PHPUnit_Framework_MockObject_MockObject $calculator */
        $calculator = $this->createMock(InventoryBalancerTriggerCalculator::class);

        $calculator
            ->expects($this->once())
            ->method('isBalanceThresholdReached')
            ->with($this->createMock(InventoryLevel::class))
            ->willReturn(true);

        $listener = new VirtualInventoryLevelUpdateAfterEventListener(
            $this->producer,
            $calculator,
            $this->repository
        );

        $listener->handleInventoryUpdateAfterEvent($event);
    }

    /**
     * @param InventoryUpdateContext $context
     * @return VirtualInventoryUpdateEvent
     */
    protected function prepareEvent(InventoryUpdateContext $context)
    {
        return new VirtualInventoryUpdateEvent($context);
    }
}
