<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Model\ChannelAwareInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\InventoryBundle\Event\VirtualInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Model\VirtualInventory\VirtualInventoryHandler;

class VirtualInventoryManager implements InventoryManagerInterface
{
    /** @var InventoryUpdateContextValidator $contextValidator */
    protected $contextValidator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var VirtualInventoryHandler $handler */
    protected $handler;

    /**
     * @deprecated use updateInventoryLevel instead
     * @param InventoryUpdateContext $context
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        $this->updateInventoryLevel($context);
    }

    /**
     * Update the virtual inventory levels to keep track of inventory needed to be reserved and available
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevel(InventoryUpdateContext $context)
    {
        $this->eventDispatcher->dispatch(
            VirtualInventoryUpdateEvent::VIRTUAL_UPDATE_BEFORE,
            new VirtualInventoryUpdateEvent($context)
        );

        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('InventoryUpdateContext structure not valid.');
        }

        if (!$context->getRelatedEntity() instanceof ChannelAwareInterface) {
            throw new \Exception('Cannot determine origin when the entity is not aware of SalesChannel(s)');
        }

        $entity = $context->getRelatedEntity();
        $salesChannelGroup = $this->getSalesChannelGroupFromEntity($entity);
        $context->setValue('salesChannelGroup', $salesChannelGroup);
        if (!$salesChannelGroup) {
            throw new \Exception('No SalesChannelGroups(s)');
        }

        $product = $context->getProduct();
        /** @var VirtualInventoryLevel $level */
        $level = $this->handler->findExistingVirtualInventory($product, $salesChannelGroup);
        if (!$level) {
            $level = $this->handler->createVirtualInventory($product, $salesChannelGroup);
        }

        $level = $this->updateReservedInventory($level, $context->getAllocatedInventory());
        $level = $this->updateInventory($level, $context);
        $this->handler->saveVirtualInventory($level, true);

        $context->setValue('virtualInventoryLevel', $level);
        
        $this->eventDispatcher->dispatch(
            VirtualInventoryUpdateEvent::VIRTUAL_UPDATE_AFTER,
            new VirtualInventoryUpdateEvent($context)
        );
    }

    /**
     * @param ChannelAwareInterface $entity
     * @return SalesChannelGroup
     */
    private function getSalesChannelGroupFromEntity($entity)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $entity->getSalesChannel();
        return $salesChannel->getGroup();
    }

    /**
     * @param VirtualInventoryLevel $level
     * @param InventoryUpdateContext $context
     * @return VirtualInventoryLevel
     */
    private function updateInventory(VirtualInventoryLevel $level, $context)
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

        $level->setInventoryQty($newInventoryQty);

        return $level;
    }

    /**
     * @param VirtualInventoryLevel $level
     * @param int $allocQty
     * @return VirtualInventoryLevel
     */
    private function updateReservedInventory(VirtualInventoryLevel $level, $allocQty)
    {
        $newInventoryQty = ($level->getReservedInventoryQty() + $allocQty);
        $level->setReservedInventoryQty($newInventoryQty);
        return $level;
    }


    /**
     * Sets the context validator
     * @param InventoryUpdateContextValidator $validator
     */
    public function setContextValidator(InventoryUpdateContextValidator $validator)
    {
        $this->contextValidator = $validator;
    }

    /**
     * Sets the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Sets the virtual inventory handler
     * @param VirtualInventoryHandler $handler
     */
    public function setVirtualInventoryHandler(VirtualInventoryHandler $handler)
    {
        $this->handler = $handler;
    }
}
