<?php

namespace Marello\Bundle\InventoryBundle\Model\Allocation;

use Marello\Bundle\InventoryBundle\Entity\Allocation;

interface WarehouseNotifierInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    /** @return bool */
    public function isEnabled();

    /** @return string */
    public function getLabel();

    public function notifyWarehouse(Allocation $allocation);
}
