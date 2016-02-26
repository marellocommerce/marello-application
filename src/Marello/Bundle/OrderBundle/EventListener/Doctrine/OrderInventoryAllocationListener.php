<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\InventoryAllocation\InventoryAllocator;
use Marello\Bundle\InventoryBundle\Logging\InventoryLogger;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

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

        $loggedItems = [];

        foreach ($entity->getItems() as $item) {
            $loggedItems[] = $inventoryItem = $this->getInventoryItemToAllocate($item, $args->getEntityManager());
            $this->allocator->allocate($inventoryItem, $item->getQuantity(), $item);
        }

        $this->logger->log(
            $loggedItems,
            'order_workflow.pending',
            function (InventoryLog $log) use ($entity) {
                $log->setOrder($entity);
            }
        );
    }

    /**
     * @param OrderItem     $item
     * @param EntityManager $em
     *
     * @return InventoryItem
     */
    protected function getInventoryItemToAllocate(OrderItem $item, EntityManager $em)
    {
        $warehouse = $em
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        return $em
            ->getRepository('MarelloInventoryBundle:InventoryItem')
            ->findOrCreateByWarehouseAndProduct($warehouse, $item->getProduct());
    }
}
