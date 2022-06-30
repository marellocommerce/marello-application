<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class WarehouseGroupLinkRebalanceListener
{
    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(
        private MessageProducerInterface $messageProducer,
        private AclHelper $aclHelper
    ) {}

    /**
     * Handle incoming event
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->unitOfWork = $this->em->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityUpdates())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityUpdates());
            $this->applyCallBackForChangeSet('triggerRebalance', $records);
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
        return ($entity instanceof WarehouseChannelGroupLink);
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
     * @param WarehouseChannelGroupLink $entity
     */
    protected function triggerRebalance(WarehouseChannelGroupLink $entity)
    {
        /** @var SalesChannelGroup[] $channelsGroups */
        $channelsGroups = $entity->getSalesChannelGroups()->toArray();
        foreach ($channelsGroups as $salesChannelGroup) {
            foreach ($salesChannelGroup->getSalesChannels() as $salesChannel) {
                $products = $this->em->getRepository(Product::class)->findByChannel(
                    $salesChannel,
                    $this->aclHelper
                );
                foreach ($products as $product) {
                    $this->messageProducer->send(
                        Topics::RESOLVE_REBALANCE_INVENTORY,
                        ['product_id' => $product->getId(), 'jobId' => md5($product->getId())]
                    );
                }
            }
        }
    }
}
