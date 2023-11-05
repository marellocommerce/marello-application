<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository;
use Marello\Bundle\InventoryBundle\Event\BalancedInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\BalancedInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class BalancedInventoryUpdateAfterEventListener
{
    use JobIdGenerationTrait;

    const BALANCED_LEVEL_CONTEXT_KEY = 'balancedInventoryLevel';
    const SALESCHANNELGROUP_CONTEXT_KEY  = 'salesChannelGroup';

    public function __construct(
        private MessageProducerInterface $messageProducer,
        private InventoryBalancerTriggerCalculator $triggerCalculator,
        private BalancedInventoryRepository $repository,
        private AclHelper $aclHelper
    ) {
    }

    public function handleInventoryUpdateAfterEvent(BalancedInventoryUpdateEvent $event)
    {
        /** @var InventoryUpdateContext $context */
        $context = $event->getInventoryUpdateContext();
        if (!$context->getIsVirtual()) {
            // do nothing when context isn't for virtual inventory levels
            return;
        }

        if (!$context->getValue(self::BALANCED_LEVEL_CONTEXT_KEY)
            || !$context->getValue(self::SALESCHANNELGROUP_CONTEXT_KEY)
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'To few arguments given in the context, no %s or %s given, please check your data',
                    self::BALANCED_LEVEL_CONTEXT_KEY,
                    self::SALESCHANNELGROUP_CONTEXT_KEY
                )
            );
        }

        /** @var ProductInterface $product */
        $product = $context->getProduct();
        $level = $context->getValue(self::BALANCED_LEVEL_CONTEXT_KEY);
        $group = $context->getValue(self::SALESCHANNELGROUP_CONTEXT_KEY);
        if ($this->isRebalanceApplicable($product, $level, $group)) {
            $this->messageProducer->send(
                ResolveRebalanceInventoryTopic::getName(),
                ['product_id' => $product->getId(), 'jobId' => $this->generateJobId($product->getId())]
            );
        }
    }

    /**
     * Check if the rebalancing is applicable for the product
     * @param ProductInterface $product
     * @param BalancedInventoryLevelInterface $level
     * @param SalesChannelGroup $group
     * @return bool
     */
    protected function isRebalanceApplicable(
        ProductInterface $product,
        BalancedInventoryLevelInterface $level = null,
        SalesChannelGroup $group = null
    ) {
        if (!$level || !$group) {
            // cannot rebalance level without appropriate information to retrieve level
            return false;
        }

        if (!$level) {
            $level = $this->findExistingBalancedInventory($product, $group);
        }

        if (!$level) {
            //cannot update or calculate something when it's non-existent
            return false;
        }

        return $this->triggerCalculator->isBalanceThresholdReached($level);
    }

    /**
     * Find existing BalancedInventoryLevel
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @return BalancedInventoryRepository|object
     */
    protected function findExistingBalancedInventory(ProductInterface $product, SalesChannelGroup $group)
    {
        /** @var BalancedInventoryRepository $repository */
        return $this->repository->findExistingBalancedInventory($product, $group, $this->aclHelper);
    }
}
