<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;

class SingleWHCalculator extends AbstractWHCalculator
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
        $wh = [];
        $itemsWithQuantity = [];
        foreach ($productsByWh as $id => $whProducts) {
            $totalAllocatedQty = 0;
            foreach ($whProducts as $product) {
                if (isset($wh[$product['sku']])) {
                    $totalAllocatedQty = $wh[$product['sku']]['totalAllocatedQty'];
                }
                $totalAllocatedQty += $product['qty'];
                $itemsWithQuantity[$product['sku']] = $product['qty'];
                $wh[$product['wh']][$product['sku']] = [
                    'totalAllocatedQty' => ($totalAllocatedQty <= $product['qtyOrdered'])
                        ? $product['qty'] : ($product['qty'] - ($totalAllocatedQty - $product['qtyOrdered'])),
                    'totalQtyOrdered' => $product['qtyOrdered'],
                    'qtyGtq' => (bool)($product['qty'] >= $product['qtyOrdered'])
                ];
            }
        }

        foreach ($wh as $warehouseCode => $result) {
            // warehouse has the same count of products in the result as the items that are ordered..
            // sounds promising...
            if (count(array_keys($result)) === count($orderItemsProducts)) {
                // the array keys (flipped) (skus) from the order items are the same as the keys (skus) we produced..
                // sounds promising again
                if ((array_keys(array_flip($orderItemsProducts)) === array_keys($result))) {
                    // now check if the total allocated (our result) is the same or more than the qty that is ordered...
                    // for this result
                    $areResultsAllCorrectlyAllocatedForSingleWarehouse = true;
                    foreach ($result as $sku => $value) {
                        if (!$value['qtyGtq']) {
                            $areResultsAllCorrectlyAllocatedForSingleWarehouse = false;
                        }
                    }
                    // this result has correctly allocated all items with the quantities required for the order
                    // this seems like a valid result for a single warehouse
                    if ($areResultsAllCorrectlyAllocatedForSingleWarehouse) {
                        $results[][implode('|', array_keys($result))] = new OrderWarehouseResult(
                            [
                                OrderWarehouseResult::WAREHOUSE_FIELD => $warehouses[$warehouseCode],
                                OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems,
                                OrderWarehouseResult::ITEMS_WITH_QUANTITY_FIELD => $itemsWithQuantity
                            ]
                        );
                    }
                }
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
