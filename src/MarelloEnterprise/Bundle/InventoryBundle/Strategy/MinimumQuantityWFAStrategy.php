<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

class MinimumQuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    
    /**
     * @var array
     */
    protected $results = [];

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getLabel()
    {
        return 'marelloenterprise.inventory.strategies.min_quantity';
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouses(Order $order)
    {
        $productsByWh = [];
        $warehouses = [];
        $orderItems = $order->getItems();
        $orderItemsByProducts = [];
        foreach ($orderItems as $orderItem) {
            $orderItemsByProducts[$orderItem->getProduct()->getSku()] = $orderItem;
            $inventoryItems = $orderItem->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    if ($inventoryLevel->getInventoryQty() >= $orderItem->getQuantity()) {
                        $warehouse = $inventoryLevel->getWarehouse();
                        $warehouses[$warehouse->getId()] = $warehouse;
                        $productsByWh[$warehouse->getId()] [] = $inventoryItem->getProduct()->getSku();
                    }
                }
            }
        }
        uasort($productsByWh, function ($a, $b) {
            return count($b) > count($a) ? 1 : -1 ;
        });

        $this->getSingleWarehouseResults($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);

        if (count($this->results) > 0) {
            return $this->results;
        }

        $this->getMultipleWarehouseResults(
            $productsByWh,
            $orderItemsByProducts,
            $warehouses,
            array_keys($orderItemsByProducts)
        );

        usort($this->results, function ($a, $b) {
            return count($b) > count($a) ? -1 : 1 ;
        });

        return array_filter($this->results, function ($result) {
            return count($result) <= count(reset($this->results));
        });
    }

    /**
     * @param array $productsByWh
     * @param array $orderItemsByProducts
     * @param array $warehouses
     * @param array $orderItems
     */
    protected function getSingleWarehouseResults($productsByWh, $orderItemsByProducts, $warehouses, $orderItems)
    {
        foreach ($productsByWh as $id => $whProducts) {
            if (count($whProducts) === count(array_keys($orderItemsByProducts)) &&
                $whProducts === array_keys($orderItemsByProducts)) {
                $this->results[][implode('|', $whProducts)] = new OrderWarehouseResult(
                    [
                        OrderWarehouseResult::WAREHOUSE_FIELD => $warehouses[$id],
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => $orderItems
                    ]
                );
            }
        }
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
