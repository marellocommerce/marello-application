<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Model\ChannelAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Model\VirtualInventory\VirtualInventoryHandler;

class VirtualInventoryManager implements InventoryManagerInterface
{
    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var InventoryUpdateContextValidator $contextValidator */
    protected $contextValidator;

    /** @var VirtualInventoryHandler $handler */
    protected $handler;

    /**
     * @deprecated use updateInventoryLevels instead
     * @param InventoryUpdateContext $context
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        $this->updateInventoryLevels($context);
    }

    /**
     * Update the virtual inventory levels to keep track of inventory needed to be reserved and available
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevels(InventoryUpdateContext $context)
    {
        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('InventoryUpdateContext structure not valid.');
        }

        if (!$context->getRelatedEntity() instanceof ChannelAwareInterface) {
            throw new \Exception('Cannot determine origin if entity is not aware of SalesChannel(s)');
        }

        $entity = $context->getRelatedEntity();
        $salesChannelGroup = $this->getSalesChannelGroupFromEntity($entity);
        if (!$salesChannelGroup) {
            throw new \Exception('No SalesChannelGroups(s)');
        }

        $product = $context->getProduct();
        /** @var VirtualInventoryLevel $level */
        $level = $this->handler->findExistingVirtualInventory($product, $salesChannelGroup);
        if (!$level) {
            $level = $this->handler->createVirtualInventory($product, $salesChannelGroup, 0);
        }

        $level = $this->updateAllocatedInventory($level, $context->getAllocatedInventory());
        $level = $this->updateInventory($level, $context->getAllocatedInventory());
        $this->handler->saveVirtualInventory($level, true);
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
     * @param int $allocQty
     * @return VirtualInventoryLevel
     */
    private function updateInventory(VirtualInventoryLevel $level, $allocQty)
    {
        if ($level->getAllocatedInventory() >= 0) {
            $newInventoryQty = ($level->getInventory() - $allocQty);
        } else {
            $newInventoryQty = ($level->getInventory() + $allocQty);
        }

        file_put_contents(
            '/var/www/app/logs/debug.log',
            print_r(__METHOD__. " #".__LINE__. " " . $newInventoryQty, true) . "\r\n",
            FILE_APPEND
        );

        $level->setInventory($newInventoryQty);

        return $level;
    }

    /**
     * @param VirtualInventoryLevel $level
     * @param int $allocQty
     * @return VirtualInventoryLevel
     */
    private function updateAllocatedInventory(VirtualInventoryLevel $level, $allocQty)
    {
        $newInventoryQty = ($level->getAllocatedInventory() + $allocQty);
        $level->setAllocatedInventory($newInventoryQty);
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
     * Sets the doctrine helper
     *
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
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
