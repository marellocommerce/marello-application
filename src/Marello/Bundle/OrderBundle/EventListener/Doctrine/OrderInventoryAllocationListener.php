<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\InventoryAllocation\InventoryAllocator;
use Marello\Bundle\InventoryBundle\Logging\InventoryLogger;
use Marello\Bundle\OrderBundle\Entity\Order;

class OrderInventoryAllocationListener
{
    /** @var InventoryAllocator */
    protected $allocator;

    /** @var InventoryLogger */
    protected $logger;

    /**
     * OrderInventoryAllocationListener constructor.
     *
     * @param InventoryAllocator $allocator
     * @param InventoryLogger    $logger
     */
    public function __construct(InventoryAllocator $allocator, InventoryLogger $logger)
    {
        $this->allocator = $allocator;
        $this->logger    = $logger;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Order) {
            return;
        }

        /*
         * TODO: Create strategy to determine from where and how to select warehouse.
         */
        $warehouse = $args->getEntityManager()
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        $items = [];
        foreach ($entity->getItems() as $item) {
            $inventoryItem = $args->getEntityManager()
                ->getRepository('MarelloInventoryBundle:InventoryItem')
                ->findOneByWarehouseAndProduct($warehouse, $item->getProduct());

            $this->allocator->allocate($inventoryItem, $item->getQuantity(), $item);
            $items[] = $inventoryItem;
        }

        $this->logger->log($items, 'workflow');
    }
}
