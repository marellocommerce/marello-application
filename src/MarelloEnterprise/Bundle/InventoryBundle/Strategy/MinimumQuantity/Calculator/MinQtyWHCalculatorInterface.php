<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

interface MinQtyWHCalculatorInterface
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
