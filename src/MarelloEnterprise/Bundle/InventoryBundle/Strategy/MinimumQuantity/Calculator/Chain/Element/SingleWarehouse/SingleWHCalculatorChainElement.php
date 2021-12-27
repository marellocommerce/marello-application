<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\SingleWarehouse;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\AbstractWHCalculatorChainElement;

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
        $orderItemsProducts = array_map(
            function ($sku) {
                return strstr($sku, '_|_', true);
            },
            array_keys($orderItemsByProducts)
        );

        foreach ($productsByWh as $id => $whProducts) {
            if (count($whProducts) === count($orderItemsProducts) &&
                $whProducts === $orderItemsProducts) {
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
            return $this->usort($results);
        } elseif ($this->getSuccessor() && count($results) === 0) {
            return $this->getSuccessor()->calculate($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);
        }
        
        return [];
    }
}
