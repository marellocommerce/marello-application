<?php

namespace Marello\Bundle\SalesBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

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
        private MessageProducerInterface $messageProducer,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritdoc}
     * @param OnFlushEventArgs $eventArgs
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
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
        foreach ($entity->getSalesChannels() as $salesChannel) {
            $products = $this->em->getRepository(Product::class)->findByChannel(
                $salesChannel,
                $this->aclHelper
            );
            /** @var Product $product */
            foreach ($products as $product) {
                $this->messageProducer->send(
                    Topics::RESOLVE_REBALANCE_INVENTORY,
                    ['product_id' => $product->getId(), 'jobId' => md5($product->getId())]
                );
            }
        }
    }
}
