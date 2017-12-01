<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Event\VirtualInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\InventoryBundle\Model\VirtualInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Entity\Repository\VirtualInventoryRepository;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class VirtualInventoryLevelUpdateAfterEventListener
{
    const VIRTUAL_LEVEL_CONTEXT_KEY = 'virtualInventoryLevel';
    const SALESCHANNELGROUP_CONTEXT_KEY  = 'salesChannelGroup';

    /** @var MessageProducerInterface $messageProducer */
    private $messageProducer;

    /** @var InventoryBalancerTriggerCalculator $triggerCalculator */
    private $triggerCalculator;

    /** @var VirtualInventoryRepository $repository */
    private $repository;

    /**
     * VirtualInventoryUpdateAfterEventListener constructor.
     * @param MessageProducerInterface $messageProducer
     * @param InventoryBalancerTriggerCalculator $triggerCalculator
     * @param VirtualInventoryRepository $repository
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        InventoryBalancerTriggerCalculator $triggerCalculator,
        VirtualInventoryRepository $repository
    ) {
        $this->messageProducer = $messageProducer;
        $this->triggerCalculator = $triggerCalculator;
        $this->repository = $repository;
    }

    /**
     * Handle incoming event
     * @param VirtualInventoryUpdateEvent $event
     * @return mixed
     */
    public function handleInventoryUpdateAfterEvent(VirtualInventoryUpdateEvent $event)
    {
        /** @var InventoryUpdateContext $context */
        $context = $event->getInventoryUpdateContext();
        if (!$context->getIsVirtual()) {
            // do nothing when context isn't for virtual inventory levels
            return;
        }

        if (!$context->getValue(self::VIRTUAL_LEVEL_CONTEXT_KEY)
            || !$context->getValue(self::SALESCHANNELGROUP_CONTEXT_KEY)
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'To few arguments given in the context, no %s or %s given, please check your data',
                    self::VIRTUAL_LEVEL_CONTEXT_KEY,
                    self::SALESCHANNELGROUP_CONTEXT_KEY
                )
            );
        }

        /** @var ProductInterface $product */
        $product = $context->getProduct();
        $level = $context->getValue(self::VIRTUAL_LEVEL_CONTEXT_KEY);
        $group = $context->getValue(self::SALESCHANNELGROUP_CONTEXT_KEY);
        if ($this->isRebalanceApplicable($product, $level, $group)) {
            $this->messageProducer->send(
                Topics::RESOLVE_REBALANCE_INVENTORY,
                ['product_id' => $product->getId(), 'jobId' => md5($product->getId())]
            );
        }
    }

    /**
     * Check if the rebalancing is applicable for the product
     * @param ProductInterface $product
     * @param VirtualInventoryLevelInterface $level
     * @param SalesChannelGroup $group
     * @return bool
     */
    protected function isRebalanceApplicable(
        ProductInterface $product,
        VirtualInventoryLevelInterface $level = null,
        SalesChannelGroup $group = null
    ) {
        if (!$level || !$group) {
            // cannot rebalance level without appropriate information to retrieve level
            return false;
        }

        if (!$level) {
            $level = $this->findExistingVirtualInventory($product, $group);
        }

        if (!$level) {
            //cannot update or calculate something when it's non-existent
            return false;
        }

        return $this->triggerCalculator->isBalanceThresholdReached($level);
    }

    /**
     * Find existing VirtualInventoryLevel
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @return VirtualInventoryLevel|object
     */
    protected function findExistingVirtualInventory(ProductInterface $product, SalesChannelGroup $group)
    {
        /** @var VirtualInventoryRepository $repository */
        return $this->repository->findExistingVirtualInventory($product, $group);
    }
}
