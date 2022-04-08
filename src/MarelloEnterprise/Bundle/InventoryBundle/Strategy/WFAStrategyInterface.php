<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

interface WFAStrategyInterface
{
    // estimation method (public function setEstimation($estimation = false)
    // will be included in 3.0, not in 2.2 because of BC breaks

    /**
     * @return string|int
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @param OrderWarehouseResult[] $initialResults
     * @return OrderWarehouseResult[]|null
     */
    public function getWarehouseResults(Order $order, Allocation $allocation = null, array $initialResults = []);
}
