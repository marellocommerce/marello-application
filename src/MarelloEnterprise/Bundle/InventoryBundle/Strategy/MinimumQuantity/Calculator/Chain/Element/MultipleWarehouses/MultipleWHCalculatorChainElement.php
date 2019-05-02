<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\
AbstractWHCalculatorChainElement;

class MultipleWHCalculatorChainElement extends AbstractWHCalculatorChainElement
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     * {@inheritdoc}
     */
    public function calculate(
        array $productsByWh,
        array $orderItemsByProducts,
        array $warehouses,
        Collection $orderItems
    ) {
        $this->getMultipleWarehouseResults(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            array_keys($orderItemsByProducts)
        );
        
        if (count($this->results) <= 1) {
            return $this->results;
        }

        usort($this->results, function ($a, $b) {
            return count($b) > count($a) ? -1 : 1 ;
        });

        $finalResults = array_filter($this->results, function ($result) {
            return count($result) <= count(reset($this->results));
        });

        usort($finalResults, function ($a, $b) {
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

        return $finalResults;
    }

    /**
     * @param array $productsByWh
     * @param array $orderItemsByProducts
     * @param array $warehouses
     * @param array $products
     * @param int|null $idx
     */
    protected function getMultipleWarehouseResults(
        $productsByWh,
        $orderItemsByProducts,
        $warehouses,
        $products,
        $idx = null
    ) {
        foreach ($productsByWh as $id => $whProducts) {
            $index = $idx !== null ? $idx : count($this->results);
            $matchedProducts = array_intersect($products, $whProducts);
            if (count($matchedProducts) > 0) {
                $matchedOrderItems = new ArrayCollection();
                foreach ($matchedProducts as $productId) {
                    $matchedOrderItems->add($orderItemsByProducts[$productId]);
                }
                $this->results[$index][implode('|', $matchedProducts)] = new OrderWarehouseResult(
                    [
                        OrderWarehouseResult::WAREHOUSE_FIELD => $warehouses[$id],
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => $matchedOrderItems
                    ]
                );
                $products_diff = array_diff($products, $whProducts);
                if (count($products_diff) > 0) {
                    $this->getMultipleWarehouseResults(
                        $productsByWh,
                        $orderItemsByProducts,
                        $warehouses,
                        $products_diff,
                        $index
                    );
                } else {
                    $idx = null;
                    break;
                }
            }
        }
    }
}
