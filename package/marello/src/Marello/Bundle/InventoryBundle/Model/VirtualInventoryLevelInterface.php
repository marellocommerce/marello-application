<?php

namespace Marello\Bundle\InventoryBundle\Model;

interface VirtualInventoryLevelInterface
{
    /**
     * @return int
     */
    public function getInventoryQty();

    /**
     * @param int
     */
    public function setInventoryQty($quantity);

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
