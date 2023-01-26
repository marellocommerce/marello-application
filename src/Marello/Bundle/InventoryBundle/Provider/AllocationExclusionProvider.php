<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\Collection;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Allocation;

class AllocationExclusionProvider implements AllocationExclusionInterface
{
    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @return Collection
     */
    public function getItems(Order $order, Allocation $allocation = null): Collection
    {
        if ($allocation) {
            return $allocation->getItems();
        }

        // option to filter orderItems for excluding items from allocating
        return $order->getItems()->filter(function (OrderItem $item) use ($order) {
            return !$item->isAllocationExclusion();
        });
    }
}
