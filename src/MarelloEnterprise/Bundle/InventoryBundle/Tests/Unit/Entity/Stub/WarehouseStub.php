<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Entity\Stub;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseStub extends Warehouse
{
    /** @var boolean */
    private $isConsolidationWarehouse;

    /**
     * @param boolean $isConsolidationWarehouse
     *
     * @return $this
     */
    public function setIsConsolidationWarehouse($isConsolidationWarehouse)
    {
        $this->isConsolidationWarehouse = $isConsolidationWarehouse;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsConsolidationWarehouse()
    {
        return $this->isConsolidationWarehouse;
    }
}
