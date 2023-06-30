<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Model\SalesChannelAwareInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Event\BalancedInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler;

class BalancedInventoryManager implements InventoryManagerInterface
{
    /** @var InventoryUpdateContextValidator $contextValidator */
    protected $contextValidator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var BalancedInventoryHandler $handler */
    protected $handler;

    public function __construct(
        BalancedInventoryHandler $handler,
        InventoryUpdateContextValidator $contextValidator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->handler = $handler;
        $this->contextValidator = $contextValidator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Update the balanced inventory levels to keep track of inventory needed to be reserved and available
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevel(InventoryUpdateContext $context): void
    {
        $this->eventDispatcher->dispatch(
            new BalancedInventoryUpdateEvent($context),
            BalancedInventoryUpdateEvent::BALANCED_UPDATE_BEFORE
        );

        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('InventoryUpdateContext structure not valid.');
        }

        if (!$context->getRelatedEntity() instanceof SalesChannelAwareInterface) {
            throw new \Exception('Cannot determine origin when the entity is not aware of SalesChannel(s)');
        }

        $entity = $context->getRelatedEntity();
        $salesChannelGroup = $this->getSalesChannelGroupFromEntity($entity);
        $context->setValue('salesChannelGroup', $salesChannelGroup);
        if (!$salesChannelGroup) {
            throw new \Exception('No SalesChannelGroups(s)');
        }

        $product = $context->getProduct();
        /** @var BalancedInventoryLevel $level */
        $level = $this->handler->findExistingBalancedInventory($product, $salesChannelGroup);
        if (!$level) {
            $level = $this->handler->createBalancedInventoryLevel($product, $salesChannelGroup);
        }

        $level = $this->updateReservedInventory($level, $context->getAllocatedInventory());
        $level = $this->updateBalancedInventory($level, $context);
        $this->handler->saveBalancedInventory($level, true);

        $context->setValue('balancedInventoryLevel', $level);
        
        $this->eventDispatcher->dispatch(
            new BalancedInventoryUpdateEvent($context),
            BalancedInventoryUpdateEvent::BALANCED_UPDATE_AFTER
        );
    }

    /**
     * @param SalesChannelAwareInterface $entity
     * @return SalesChannelGroup
     */
    private function getSalesChannelGroupFromEntity($entity)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $entity->getSalesChannel();
        return $salesChannel->getGroup();
    }

    /**
     * @param BalancedInventoryLevel $level
     * @param InventoryUpdateContext $context
     * @return BalancedInventoryLevel
     */
    private function updateBalancedInventory(BalancedInventoryLevel $level, $context)
    {
        $allocQty = $context->getAllocatedInventory();
        $inventoryQty = $context->getInventory();

        if (!$inventoryQty && ($allocQty > 0)) {
            $newInventoryQty = ($level->getInventoryQty() - $allocQty);
        } elseif (!$inventoryQty && $allocQty < 0) {
            $newInventoryQty = ($level->getInventoryQty() + (-1 * $allocQty));
        } else {
            $newInventoryQty = $level->getInventoryQty();
        }

        if ($newInventoryQty < 0) {
            $newInventoryQty = 0;
        }

        $level->setInventoryQty($newInventoryQty);

        return $level;
    }

    /**
     * @param BalancedInventoryLevel $level
     * @param int $allocQty
     * @return BalancedInventoryLevel
     */
    private function updateReservedInventory(BalancedInventoryLevel $level, $allocQty)
    {
        $newInventoryQty = ($level->getReservedInventoryQty() + $allocQty);
        $level->setReservedInventoryQty($newInventoryQty);
        return $level;
    }
}
