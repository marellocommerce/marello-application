<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order)
    {
        $productsBySku = [];
        $productsByWh = [];
        $productsQty = [];
        $orderItems = $order->getItems();
        foreach ($orderItems as $index => $orderItem) {
            $product = $orderItem->getProduct();
            $sku = $product->getSku();
            $productsBySku[$sku] = $product;
            $key = sprintf('%s_|_%s', $sku, $index);
            $orderItemsByProducts[$key] = $orderItem;
            $inventoryItems = $product->getInventoryItems();
            $invLevToWh = [];
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $invLevelQtyKey = sprintf('%s_|_%s', $sku, $inventoryLevel->getId());
                    $invLevelQty = $inventoryLevel->getInventoryQty();
                    if (isset($productsQty[$invLevelQtyKey])) {
                        $invLevelQty = $invLevelQty - $productsQty[$invLevelQtyKey];
                    }
                    $warehouse = $inventoryLevel->getWarehouse();
                    $invLevToWh[$warehouse->getId()] = $inventoryLevel;
                    $warehouseType = $warehouse->getWarehouseType()->getName();
                    if (($invLevelQty >= $orderItem->getQuantity() ||
                            $warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL)) {
                        $productsByWh[$key] [] = $warehouse;
                    }
                }
            }
            usort($productsByWh[$key], function(Warehouse $a, Warehouse $b) use ($product){
                if ($a->isDefault() === true) {
                    return -1;
                } elseif ($b->isDefault() === true) {
                    return 1;
                } else {
                    $aWhType = $a->getWarehouseType()->getName();
                    $bWhType = $b->getWarehouseType()->getName();
                    $externalType = WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL;
                    if ($aWhType !== $externalType && $bWhType === $externalType) {
                        return -1;
                    } elseif ($aWhType === $externalType && $bWhType !== $externalType) {
                        return 1;
                    } elseif ($aWhType !== $externalType && $bWhType !== $externalType) {
                        return 0;
                    } elseif ($aWhType === $externalType && $bWhType === $externalType) {
                        $aWhCode = $a->getCode();
                        $bWhCode = $b->getCode();
                        $supplier = $product->getPreferredSupplier();
                        if ($supplier) {
                            if (strpos($aWhCode, str_replace(' ', '_', strtolower($supplier->getName())) !== false)) {
                                return -1;
                            } elseif (strpos($bWhCode, str_replace(' ', '_', strtolower($supplier->getName())) !== false)) {
                                return 1;
                            }
                        }
                    }
                }

                return 0;
            });
            $productsByWh[$key] = reset($productsByWh[$key]);
            $invLevel = $invLevToWh[$productsByWh[$key]->getId()];
            $invLevelQtyKey = sprintf('%s_|_%s', $sku, $invLevel->getId());
            $productsQty[$invLevelQtyKey] =
                (isset($productsQty[$invLevelQtyKey]) ? $productsQty[$invLevelQtyKey] : 0) + $orderItem->getQuantity();
        }
        $whByProducts = [];
        $whByIds = [];
        /** @var Warehouse $wh */
        foreach ($productsByWh as $key => $wh) {
            $whByIds[$wh->getId()] = $wh;
            $whByProducts[$wh->getId()][] = $key;
        }
        $result = [];
        foreach ($whByProducts as $whId => $items) {
            $resultItem = [];
            $resultItem[OrderWarehouseResult::WAREHOUSE_FIELD] = $whByIds[$whId];
            $resultItem[OrderWarehouseResult::ORDER_ITEMS_FIELD] = new ArrayCollection([]);
            foreach ($items as $item) {
                $index = explode('_|_', $item)[1];
                $resultItem[OrderWarehouseResult::ORDER_ITEMS_FIELD]->add($orderItems[$index]);
            }
            $result[] = new OrderWarehouseResult($resultItem);
        }
        return $result;
    }
}
