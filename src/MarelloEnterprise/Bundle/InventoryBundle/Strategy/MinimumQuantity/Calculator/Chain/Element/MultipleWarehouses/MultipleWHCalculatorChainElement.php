<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\MultipleWarehouses;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\Chain\Element\AbstractWHCalculatorChainElement;

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
        $products = array_map(
            function ($sku) {
                return strstr($sku, '_|_', true);
            },
            array_keys($orderItemsByProducts)
        );
        $this->getMultipleWarehouseResults(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            $products
        );

        if (count($this->results) >= 1) {
            if (count($this->results) === 1) {
                return $this->results;
            }

            usort($this->results, function ($a, $b) {
                return count($b) > count($a) ? -1 : 1;
            });

            $finalResults = array_filter($this->results, function ($result) {
                return count($result) <= count(reset($this->results));
            });
            
            return $this->usort($finalResults);
        } elseif ($this->getSuccessor()) {
            return $this->getSuccessor()->calculate($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);
        }

        return [];
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
                    foreach ($orderItemsByProducts as $combinedSku => $orderItem) {
                        $sku = strstr($combinedSku, '_|_', true);
                        if ($productId === $sku && !$matchedOrderItems->contains($orderItem)) {
                            $matchedOrderItems->add($orderItem);
                        }
                    }
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
