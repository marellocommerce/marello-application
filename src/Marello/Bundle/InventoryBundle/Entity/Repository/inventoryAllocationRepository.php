<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class InventoryAllocationRepository extends EntityRepository
{

    /**
     * @param InventoryItem $inventoryItem
     * @param OrderItem     $orderItem
     *
     * @return null|object
     */
    public function findOneByInventoryItemAndOrderItem(InventoryItem $inventoryItem, OrderItem $orderItem)
    {
        return $this->findOneBy(compact('inventoryItem', 'orderItem'));
    }
}
