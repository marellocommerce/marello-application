<?php

namespace MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Marello\Bundle\OrderBundle\Entity\Order;
use MarelloEnterprise\Bundle\OrderBundle\Provider\OrderConsolidationProvider;

class OnOrderCreateListener
{
    /** @var EntityManagerInterface $em */
    protected EntityManagerInterface $em;

    public function __construct(
        protected OrderConsolidationProvider $consolidationProvider
    ) {
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @return void
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $this->em = $eventArgs->getEntityManager();
        $unitOfWork = $this->em->getUnitOfWork();

        if (!empty($unitOfWork->getScheduledEntityInsertions())) {
            $records = $this->filterRecords($unitOfWork->getScheduledEntityInsertions());
            $this->applyCallBackForChangeSet('setConsolidationOptionForOrder', $records);
        }
        if (!empty($unitOfWork->getScheduledEntityUpdates())) {
            $records = $this->filterRecords($unitOfWork->getScheduledEntityUpdates());
            $this->applyCallBackForChangeSet('setConsolidationOptionForOrder', $records);
        }
    }

    /**
     * @param array $records
     * @return array
     */
    protected function filterRecords(array $records): array
    {
        return array_filter($records, [$this, 'getIsEntityInstanceOf']);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function getIsEntityInstanceOf($entity): bool
    {
        return ($entity instanceof Order);
    }

    /**
     * @param $callback
     * @param array $changeSet
     * @return void
     * @throws \Exception
     */
    protected function applyCallBackForChangeSet($callback, array $changeSet): void
    {
        try {
            array_walk($changeSet, [$this, $callback]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return void
     */
    protected function setConsolidationOptionForOrder(Order $order): void
    {
        $order->setConsolidationEnabled(false);
        if ($this->consolidationProvider->isConsolidationEnabledForOrder($order)) {
            $order->setConsolidationEnabled(true);
        }
    }
}
