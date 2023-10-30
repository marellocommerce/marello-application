<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManagerInterface;
use Marello\Bundle\ProductBundle\Entity\Product;

class OnProductCreateEventListener
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

        if (!empty($this->unitOfWork->getScheduledEntityInsertions())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityInsertions());
            $this->applyCallBackForChangeSet('createInventoryItem', $records);
        }
    }

    /**
     * @param Product $entity
     */
    protected function createInventoryItem(Product $entity)
    {
        $result = $this->inventoryItemManager->createInventoryItem($entity);
        if ($result) {
            $inventoryItem = $this->checkReplenishment($result);
            $inventoryItem = $this->checkProductUnit($inventoryItem);
            $this->em->persist($inventoryItem);
            $classMeta = $this->em->getClassMetadata(get_class($inventoryItem));
            $this->unitOfWork->computeChangeSet($classMeta, $inventoryItem);
        }
    }

    /**
     * @param InventoryItem $item
     * @return mixed
     */
    protected function checkReplenishment($item)
    {
        if (!$item->getReplenishment()) {
            // get default replenishment option
            $replenishment = $this->inventoryItemManager->getDefaultReplenishment();
            if ($replenishment) {
                $item->setReplenishment($replenishment);
            }
        }

        return $item;
    }

    /**
     * @param InventoryItem $item
     * @return mixed
     */
    protected function checkProductUnit($item)
    {
        if (!$item->getProductUnit()) {
            // get default product unit option
            $productUnit = $this->inventoryItemManager->getDefaultProductUnit();
            if ($productUnit) {
                $item->setProductUnit($productUnit);
            }
        }

        return $item;
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
