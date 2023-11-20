<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\SalesBundle\Async\Topic\RebalanceSalesChannelGroupTopic;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class SalesChannelGroupInventoryRebalanceListener
{
    /**
     * @var UnitOfWork
     *
     */
    protected $unitOfWork;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(
        private MessageProducerInterface $messageProducer
    ) {
    }

    /**
     * {@inheritdoc}
     * @param OnFlushEventArgs $eventArgs
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getObjectManager();
        $this->unitOfWork = $this->em->getUnitOfWork();
        $records = [];
        if (!empty($this->unitOfWork->getScheduledEntityInsertions())) {
            $records = array_merge($records, $this->filterRecords($this->unitOfWork->getScheduledEntityInsertions()));
        }

        if (!empty($this->unitOfWork->getScheduledEntityDeletions())) {
            $records = array_merge($records, $this->filterRecords($this->unitOfWork->getScheduledEntityDeletions()));
        }

        if (!empty($records)) {
            $this->applyCallBackForChangeSet('rebalanceForSalesChannelGroup', $records);
        }
    }

    /**
     * {@inheritdoc}
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
        return ($entity instanceof SalesChannelGroup);
    }

    /**
     * {@inheritdoc}
     * @param SalesChannelGroup $entity
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function rebalanceForSalesChannelGroup(SalesChannelGroup $entity)
    {
        $salesChannelIds = [];
        foreach ($entity->getSalesChannels() as $salesChannel) {
            $salesChannelIds[] = $salesChannel->getId();
        }
        $this->messageProducer->send(
            RebalanceSalesChannelGroupTopic::getName(),
            ['salesChannelIds' => $salesChannelIds]
        );
    }
}
