<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

interface WFAStrategyInterface
{
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
     * @param OrderWarehouseResult[] $initialResults
     * @return OrderWarehouseResult[]|null
     */
    public function getWarehouseResults(Order $order, array $initialResults = []);
}
