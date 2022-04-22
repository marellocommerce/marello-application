<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator;

use Doctrine\Common\Collections\Collection;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

interface QtyWHCalculatorInterface
{
    /**
     * @param array $productsByWh
     * @param array $orderItemsByProducts
     * @param Warehouse[] $warehouses
     * @param Collection $orderItems
     */
    public function calculate(
        array $productsByWh,
        array $orderItemsByProducts,
        array $warehouses,
        Collection $orderItems
    );
}
