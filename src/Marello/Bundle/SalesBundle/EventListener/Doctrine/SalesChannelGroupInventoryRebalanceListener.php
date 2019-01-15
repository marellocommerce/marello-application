<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Async\Topics;
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

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * Handle incoming event
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->unitOfWork = $this->em->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityInsertions())) {
            if (!empty($this->filterRecords($this->unitOfWork->getScheduledEntityInsertions()))) {
                $this->triggerRebalance();
            }
        }
        if (!empty($this->unitOfWork->getScheduledEntityDeletions())) {
            if (!empty($this->filterRecords($this->unitOfWork->getScheduledEntityDeletions()))) {
                $this->triggerRebalance();
            }
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

    protected function triggerRebalance()
    {
        $this->messageProducer->send(
            Topics::RESOLVE_REBALANCE_ALL_INVENTORY,
            Topics::ALL_INVENTORY
        );
    }
}
