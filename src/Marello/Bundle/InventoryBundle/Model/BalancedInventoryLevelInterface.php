<?php

namespace Marello\Bundle\InventoryBundle\Model;

interface BalancedInventoryLevelInterface extends InventoryQtyAwareInterface
{
    /**
     * @return int
     */
    public function getBalancedInventoryQty();

    /**
     * @param int
     */
    public function setBalancedInventoryQty($quantity);

    /**
     * @return int
     */
    public function getReservedInventoryQty();

    /**
     * @param int
     */
    public function setReservedInventoryQty($quantity);
}
