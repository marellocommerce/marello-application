<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\
AbstractWHCalculatorChainElement;

class SingleWHCalculatorChainElement extends AbstractWHCalculatorChainElement
{
    /**
     * {@inheritdoc}
     */
    public function calculate(
        array $productsByWh,
        array $orderItemsByProducts,
        array $warehouses,
        Collection $orderItems
    ) {
        $results = [];
        foreach ($productsByWh as $id => $whProducts) {
            if (count($whProducts) === count(array_keys($orderItemsByProducts)) &&
                $whProducts === array_keys($orderItemsByProducts)) {
                $results[][implode('|', $whProducts)] = new OrderWarehouseResult(
                    [
                        OrderWarehouseResult::WAREHOUSE_FIELD => $warehouses[$id],
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems
                    ]
                );
            }
        }
        if (count($results) === 1) {
            return $results;
        } elseif (count($results) > 1) {
            usort($results, function ($a, $b) {
                $aHasDefaultWh = $this->hasDefaultWarehouse($a);
                $bHasDefaultWh = $this->hasDefaultWarehouse($b);

                if (($aHasDefaultWh && $bHasDefaultWh) || (!$aHasDefaultWh && !$bHasDefaultWh)) {
                    return 0;
                } elseif ($aHasDefaultWh && !$bHasDefaultWh) {
                    return -1;
                } else {
                    return 1;
                }
            });

            return $results;
        } elseif ($this->getSuccessor() && count($results) === 0) {
            return $this->getSuccessor()->calculate($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);
        }
        
        return [];
    }
}
