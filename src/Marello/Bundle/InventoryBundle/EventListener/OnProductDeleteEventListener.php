<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManagerInterface;
use Marello\Bundle\ProductBundle\Entity\Product;

class OnProductDeleteEventListener
{
    /**
     * @var InventoryItemManagerInterface
     */
    protected $inventoryItemManager;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * InventoryLevelEventListener constructor.
     * @param InventoryItemManagerInterface $manager
     */
    public function __construct(InventoryItemManagerInterface $manager)
    {
        $this->inventoryItemManager = $manager;
    }

    /**
     * Handle incoming event
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getObjectManager();
        $this->unitOfWork = $this->em->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityDeletions())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityDeletions());
            $this->applyCallBackForChangeSet('deleteInventoryItemFromProduct', $records);
        }
    }

    /**
     * @param Product $entity
     */
    protected function deleteInventoryItemFromProduct(Product $entity)
    {
        $item = $this->inventoryItemManager->getInventoryItemToDelete($entity);
        if (!$item) {
            return;
        }
        $this->em->persist($item);
        $classMeta = $this->em->getClassMetadata(get_class($item));
        $this->unitOfWork->computeChangeSet($classMeta, $item);
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
}
