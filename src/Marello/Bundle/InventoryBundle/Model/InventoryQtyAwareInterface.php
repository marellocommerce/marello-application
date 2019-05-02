<?php

namespace Marello\Bundle\InventoryBundle\Model;

interface InventoryQtyAwareInterface
{
    /**
     * @return int
     */
    public function getInventoryQty();

    /**
     * @param int
     */
    public function setInventoryQty($quantity);
}
