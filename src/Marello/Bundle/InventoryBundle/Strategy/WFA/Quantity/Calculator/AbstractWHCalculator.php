<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA\Quantity\Calculator;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\ProductBundle\Entity\Product;

abstract class AbstractWHCalculator implements QtyWHCalculatorInterface
{
    const HAS_QUANTITY = 'has_quantity';
    const ITEMS_QTY = 'items_quantity';
    const PRIORITY = 'priority';

    /**
     * @var QtyWHCalculatorInterface|null
     */
    private $successor;

    /**
     * @param QtyWHCalculatorInterface $whCalculator
     */
    public function setSuccessor(QtyWHCalculatorInterface $whCalculator)
    {
        $this->successor = $whCalculator;
    }

    /**
     * @return QtyWHCalculatorInterface|null
     */
    protected function getSuccessor()
    {
        return $this->successor;
    }
    
    /**
     * @param OrderWarehouseResult[] $results
     * @return int
     */
    protected function hasDefaultWarehouse(array $results)
    {
        $count = 0;
        foreach ($results as $result) {
            if ($result->getWarehouse()->isDefault()) {
                $count += count($result->getOrderItems()->toArray());
            }
        }
        return $count;
    }

    /**
     * @param OrderWarehouseResult[] $results
     * @return int
     */
    protected function hasNotExternalWarehouse(array $results)
    {
        $count = 0;
        foreach ($results as $result) {
            if ($result->getWarehouse()->getWarehouseType()->getName() !==
                WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL
            ) {
                $count += count($result->getOrderItems()->toArray());
            }
        }
        return $count;
    }

    /**
     * @param OrderWarehouseResult[] $results
     * @return boolean
     */
    protected function hasExternalWarehouse(array $results)
    {
        foreach ($results as $result) {
            if ($result->getWarehouse()->getWarehouseType()->getName() ===
                WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param OrderWarehouseResult $result
     * @return array
     */
    protected function getExternalWarehouseData(OrderWarehouseResult $result)
    {
        $orderItems = $result->getOrderItems();
        $warehouse = $result->getWarehouse();
        $productsQty = [];
        $hasEnoughQty = true;
        $cumulativePriority = 0;
        foreach ($orderItems as $orderItem) {
            $product = $orderItem->getProduct();
            $invItem = $product->getInventoryItem();
            $invLevel = $invItem->getInventoryLevel($warehouse);
            $invLevelQtyKey = sprintf('%s_|_%s', $product->getSku(), $invLevel->getId());
            $invLevelQty = $invLevel->getVirtualInventoryQty();
            if (isset($productsQty[$invLevelQtyKey])) {
                $invLevelQty = $invLevelQty - $productsQty[$invLevelQtyKey];
            }
            if ($invLevelQty < $orderItem->getQuantity()) {
                $hasEnoughQty = false;
            }
            $cumulativePriority += $this->getExternalWarehousePriority($product, $warehouse);
        }

        return [
            self::HAS_QUANTITY => $hasEnoughQty,
            self::ITEMS_QTY => $orderItems->count(),
            self::PRIORITY => $cumulativePriority
        ];
    }

    /**
     * @param Product $product
     * @param Warehouse $warehouse
     * @return int
     */
    protected function getExternalWarehousePriority(Product $product, Warehouse $warehouse)
    {
        $priority = 1000;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if ($productSupplierRelation->getCanDropship() === true) {
                $supplier = $productSupplierRelation->getSupplier();
                $supplierWarehouseCode = $supplier->getCode();
                if ($supplier->getCanDropship() === true && $supplierWarehouseCode === $warehouse->getCode()) {
                    return $productSupplierRelation->getPriority();
                }
            }
        }

        return $priority;
    }

    /**
     * @param OrderWarehouseResult[] $results
     * @return OrderWarehouseResult[]
     */
    protected function usort(array $results)
    {
        usort($results, function ($a, $b) {
            $aHasDefaultWh = $this->hasDefaultWarehouse($a);
            $bHasDefaultWh = $this->hasDefaultWarehouse($b);
            $aHasNotExternalWh = $this->hasNotExternalWarehouse($a);
            $bHasNotExternalWh = $this->hasNotExternalWarehouse($b);
            $aHasExternalWh = $this->hasExternalWarehouse($a);
            $bHasExternalWh = $this->hasExternalWarehouse($b);

            if ($aHasDefaultWh > $bHasDefaultWh) {
                return -1;
            } elseif ($aHasDefaultWh < $bHasDefaultWh) {
                return 1;
            } elseif ($aHasNotExternalWh > $bHasNotExternalWh) {
                return -1;
            } elseif ($aHasNotExternalWh < $bHasNotExternalWh) {
                return 1;
            } elseif ($aHasExternalWh || $bHasExternalWh) {
                if ($aHasExternalWh && !$bHasExternalWh) {
                    return 1;
                } elseif (!$aHasExternalWh && $bHasExternalWh) {
                    return -1;
                } elseif ($aHasExternalWh && $bHasExternalWh) {
                    $result = 0;
                    foreach ($a as $aResult) {
                        foreach ($b as $bResult) {
                            $aExtWhData = $this->getExternalWarehouseData($aResult);
                            $bExtWhData = $this->getExternalWarehouseData($bResult);
                            if (($aExtWhData[self::HAS_QUANTITY] === true &&
                                    $bExtWhData[self::HAS_QUANTITY] === true) ||
                                ($aExtWhData[self::HAS_QUANTITY] === false &&
                                    $bExtWhData[self::HAS_QUANTITY] === false)
                            ) {
                                if ($aExtWhData[self::PRIORITY]/$aExtWhData[self::ITEMS_QTY] <
                                    $bExtWhData[self::PRIORITY]/$bExtWhData[self::ITEMS_QTY]) {
                                    $result += -1*$aExtWhData[self::ITEMS_QTY];
                                } elseif ($aExtWhData[self::PRIORITY]/$aExtWhData[self::ITEMS_QTY] >
                                    $bExtWhData[self::PRIORITY]/$bExtWhData[self::ITEMS_QTY]) {
                                    $result += 1*$bExtWhData[self::ITEMS_QTY];
                                }
                            } elseif ($aExtWhData[self::HAS_QUANTITY] === true &&
                                $bExtWhData[self::HAS_QUANTITY] === false
                            ) {
                                $result += -10*$aExtWhData[self::ITEMS_QTY];
                            } elseif ($aExtWhData[self::HAS_QUANTITY] === false &&
                                $bExtWhData[self::HAS_QUANTITY] === true
                            ) {
                                $result += 10*$bExtWhData[self::ITEMS_QTY];
                            }
                        }
                    }

                    return $result;
                }
            }

            return 0;
        });
        
        return $results;
    }
}
