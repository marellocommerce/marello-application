<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Event\BalancedInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\BalancedInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository;
use Marello\Bundle\InventoryBundle\EventListener\BalancedInventoryUpdateAfterEventListener;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class BalancedInventoryLevelUpdateAfterEventListenerTest extends TestCase
{
    /**
     * @var InventoryUpdateContext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $inventoryUpdateContext;

    /**
     * @var BalancedInventoryUpdateAfterEventListener $listener
     */
    protected $listener;

    /**
     * @var InventoryManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $inventoryManager;

    /**
     * @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $producer;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var BalancedInventoryRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->inventoryManager = $this->createMock(InventoryManager::class);

        $this->producer = $this->createMock(MessageProducerInterface::class);
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager =  $this->createMock(ConfigManager::class);
        $calculator = new InventoryBalancerTriggerCalculator($configManager);

        $this->repository = $this->createMock(BalancedInventoryRepository::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->listener = new BalancedInventoryUpdateAfterEventListener(
            $this->producer,
            $calculator,
            $this->repository,
            $this->aclHelper
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
     */
    public function testThrowInvalidArgumentExceptionOnToFewDataGivenInContext()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(<<<EOF
To few arguments given in the context, no balancedInventoryLevel or salesChannelGroup given, please check your data
EOF
        );
        $context = new InventoryUpdateContext();
        $context->setIsVirtual(true);
        $event = $this->prepareEvent($context);

        $this->listener->handleInventoryUpdateAfterEvent($event);
    }

    public function testRebalanceThresholdHasBeenReachedAndTriggerIsBeingSend()
    {
        $context = new InventoryUpdateContext();
        /** @var BalancedInventoryLevelInterface|\PHPUnit\Framework\MockObject\MockObject $level */
        $level = $this->createMock(BalancedInventoryLevelInterface::class);

        $context->setValue(BalancedInventoryUpdateAfterEventListener::BALANCED_LEVEL_CONTEXT_KEY, $level);
        $context->setValue(
            BalancedInventoryUpdateAfterEventListener::SALESCHANNELGROUP_CONTEXT_KEY,
            $this->createMock(SalesChannelGroup::class)
        );
        $context->setProduct($this->createMock(Product::class));
        $context->setIsVirtual(true);
        $event = $this->prepareEvent($context);

        /** @var InventoryBalancerTriggerCalculator|\PHPUnit\Framework\MockObject\MockObject $calculator */
        $calculator = $this->createMock(InventoryBalancerTriggerCalculator::class);

        $calculator
            ->expects($this->once())
            ->method('isBalanceThresholdReached')
            ->with($this->createMock(BalancedInventoryLevelInterface::class))
            ->willReturn(true);

        $listener = new BalancedInventoryUpdateAfterEventListener(
            $this->producer,
            $calculator,
            $this->repository,
            $this->aclHelper
        );

        $listener->handleInventoryUpdateAfterEvent($event);
    }

    /**
     * @param InventoryUpdateContext $context
     * @return BalancedInventoryUpdateEvent
     */
    protected function prepareEvent(InventoryUpdateContext $context)
    {
        return new BalancedInventoryUpdateEvent($context);
    }
}
