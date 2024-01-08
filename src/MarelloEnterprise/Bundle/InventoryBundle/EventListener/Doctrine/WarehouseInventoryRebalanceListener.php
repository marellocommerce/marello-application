<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class WarehouseInventoryRebalanceListener
{
    use JobIdGenerationTrait;

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
    ) {
    }

    /**
     * Handle incoming event
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getObjectManager();
        $this->unitOfWork = $this->em->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityInsertions())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityInsertions());
            $this->applyCallBackForChangeSet('triggerRebalance', $records);
        }
        if (!empty($this->unitOfWork->getScheduledEntityDeletions())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityDeletions());
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
        return ($entity instanceof Warehouse);
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
     * @param Warehouse $entity
     */
    protected function triggerRebalance(Warehouse $entity)
    {
        $productIds = [];
        $group = $entity->getGroup();
        if ($group) {
            $link = $group->getWarehouseChannelGroupLink();
            if ($link) {
                /** @var SalesChannelGroup[] $channelsGroups */
                $channelsGroups = $link->getSalesChannelGroups()->toArray();
                foreach ($channelsGroups as $salesChannelGroup) {
                    foreach ($salesChannelGroup->getSalesChannels() as $salesChannel) {
                        $products = $this->em->getRepository(Product::class)->findByChannel(
                            $salesChannel,
                            $this->aclHelper
                        );
                        foreach ($products as $product) {
                            $productIds[$product->getId()] = $product->getId();
                        }
                    }
                }
                foreach ($productIds as $id) {
                    $this->messageProducer->send(
                        ResolveRebalanceInventoryTopic::getName(),
                        ['product_id' => $id, 'jobId' => $this->generateJobId($id)]
                    );
                }
            }
        }
    }
}
