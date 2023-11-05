<?php

namespace Marello\Bundle\ProductBundle\EventListener\Doctrine;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Marello\Bundle\InventoryBundle\Async\Topic\BalancedInventoryResetTopic;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class ProductUpdateInventoryRebalanceListener
{
    use JobIdGenerationTrait;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * ProductUpdateInventoryRebalanceListener constructor.
     * @param MessageProducerInterface $messageProducer
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        DoctrineHelper $doctrineHelper
    ) {
        $this->messageProducer = $messageProducer;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Handle incoming event
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getObjectManager();
        $this->unitOfWork = $entityManager->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityUpdates())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityUpdates());
            $this->applyCallBackForChangeSet('rebalanceAfterProductStatusChanged', $records);
            $this->applyCallBackForChangeSet('rebalanceAfterSalesChanelAssignment', $records);
        }
        if (!empty($this->unitOfWork->getScheduledEntityDeletions())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityDeletions());
            $this->applyCallBackForChangeSet('rebalanceAfterProductStatusChanged', $records);
        }
    }

    /**
     * @param array $records
     * @return array
     */
    protected function filterRecords(array $records)
    {
        return array_filter($records, [$this, 'getIsEntityInstanceOf']);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function getIsEntityInstanceOf($entity)
    {
        return ($entity instanceof Product);
    }

    /**
     * @param string $callback function
     * @param array $changeSet
     * @throws \Exception
     */
    protected function applyCallBackForChangeSet($callback, array $changeSet)
    {
        try {
            array_walk($changeSet, [$this, $callback]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Trigger a rebalance after a SalesChannel is being assigned to a Product
     * @param Product $entity
     */
    protected function rebalanceAfterSalesChanelAssignment(Product $entity)
    {
        /** @var PersistentCollection $collection */
        $collectionUpd = $this->unitOfWork->getScheduledCollectionUpdates();
        $processedProducts = [];
        foreach ($collectionUpd as $collection) {
            if ($collection->first() instanceof SalesChannel) {
                $unassignedSalesChannelsPerGroup = [];
                /** @var SalesChannel $salesChannel */
                foreach ($collection->getDeleteDiff() as $salesChannel) {
                    $group = $salesChannel->getGroup();
                    $unassignedSalesChannelsPerGroup[$group->getId()][] = $salesChannel;
                    $groupTotalSalesChannels = $group->getSalesChannels()->count();
                    $countUnassignedSalesChannels = count($unassignedSalesChannelsPerGroup[$group->getId()]);

                    if ($groupTotalSalesChannels === $countUnassignedSalesChannels) {
                        $repo = $this->doctrineHelper->getEntityRepositoryForClass(BalancedInventoryLevel::class);
                        /** @var BalancedInventoryLevel $result */
                        $result = $repo->findOneBy(['product' => $entity, 'salesChannelGroup' => $group]);
                        if ($result) {
                            $this->triggerBalancedInventoryReset($result);
                        }
                    }
                }

                if (count($collection->getInsertDiff()) > 0 && !in_array($entity->getId(), $processedProducts)) {
                    $this->triggerRebalance($entity);
                    $processedProducts[] = $entity->getId();
                }
            }
        }
    }

    /**
     * @param Product $entity
     */
    protected function rebalanceAfterProductStatusChanged(Product $entity)
    {
        $changeSet = $this->unitOfWork->getEntityChangeSet($entity);
        if (count($changeSet) === 0) {
            return;
        }
        if (in_array('status', array_keys($changeSet))) {
            if (isset($changeSet['status'][1])) {
                /** @var ProductStatus $productStatus */
                $productStatus = $changeSet['status'][1];
                if ($productStatus->getName() === ProductStatus::ENABLED) {
                    $this->triggerRebalance($entity);
                } elseif ($productStatus->getName() === ProductStatus::DISABLED) {
                    $repo = $this->doctrineHelper->getEntityRepositoryForClass(BalancedInventoryLevel::class);
                    $results = $repo->findBy(['product' => $entity]);
                    /** @var BalancedInventoryLevel $result */
                    foreach ($results as $result) {
                        $this->triggerBalancedInventoryReset($result);
                    }
                }
            }
        }
    }

    /**
     * @param Product $entity
     */
    protected function triggerRebalance(Product $entity)
    {
        $this->messageProducer->send(
            ResolveRebalanceInventoryTopic::getName(),
            ['product_id' => $entity->getId(), 'jobId' => $this->generateJobId($entity->getId())]
        );
    }

    /**
     * @param BalancedInventoryLevel $level
     */
    protected function triggerBalancedInventoryReset(BalancedInventoryLevel $level)
    {
        $this->messageProducer->send(
            BalancedInventoryResetTopic::getName(),
            ['blncd_inventory_level_id' => $level->getId(), 'jobId' => $this->generateJobId($level->getId())]
        );
    }
}
