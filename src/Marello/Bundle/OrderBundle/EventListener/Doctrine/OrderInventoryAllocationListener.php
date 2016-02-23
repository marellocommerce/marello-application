<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\InventoryAllocation\InventoryAllocator;
use Marello\Bundle\OrderBundle\Entity\Order;

class OrderInventoryAllocationListener
{
    /** @var InventoryAllocator */
    protected $allocator;

    /**
     * OrderInventoryAllocationListener constructor.
     *
     * @param InventoryAllocator $allocator
     */
    public function __construct(InventoryAllocator $allocator)
    {
        $this->allocator = $allocator;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Order) {
            return;
        }

        $warehouse = $args->getEntityManager()
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        foreach ($entity->getItems() as $item) {
            $inventoryItem = $args->getEntityManager()
                ->getRepository('MarelloInventoryBundle:InventoryItem')
                ->findOneByWarehouseAndProduct($warehouse, $item->getProduct());

            $this->allocator->allocate($inventoryItem, $item->getQuantity(), $item);
        }
    }
}
