<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Model\ChannelAwareInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Model\VirtualInventory\VirtualInventoryHandler;

class VirtualInventoryManager implements InventoryManagerInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var InventoryUpdateContextValidator
     */
    protected $contextValidator;

    /** @var VirtualInventoryHandler $handler */
    protected $handler;

    public function __construct(VirtualInventoryHandler $handler)
    {
        $this->handler = $handler;
    }

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
            throw new \Exception('Context structure not valid.');
        }

        if (!$context->getRelatedEntity() instanceof ChannelAwareInterface) {
            throw new \Exception('Cannot determine origin if entity is not aware of SalesChannel(s)');
        }

        $entity = $context->getRelatedEntity();
        $salesChannelGroup = $this->getSalesChannelGroupFromEntity($entity);
        if (!$salesChannelGroup) {
            return;
        }

        $product = $context->getProduct();
        /** @var VirtualInventoryLevel $level */
        $level = $this->handler->findExistingVirtualInventory($product, $salesChannelGroup);
        $currentInventoryQty = $level->getInventory();


        $this->handler->saveVirtualInventory($level, true);
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
     * @param ChannelAwareInterface $entity
     * @return SalesChannelGroup
     */
    protected function getSalesChannelGroupFromEntity($entity)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $entity->getSalesChannel();
        return $salesChannel->getGroup();
    }


    protected function isPositive($qty)
    {
        return ($qty > 0);
    }
}
