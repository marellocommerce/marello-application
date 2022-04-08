<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class OrderWarehousesProvider implements OrderWarehousesProviderInterface
{
    /**
     * @var bool
     */
    private $estimation = false;

    /**
     * {@inheritDoc}
     */
    public function setEstimation($estimation = false)
    {
        $this->estimation = $estimation;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array
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
            /** @var ArrayCollection $inventoryItems */
            $inventoryItems = $orderItem->getInventoryItems();
            $inventoryItem = $inventoryItems->first();
            $invLevToWh = [];
            $invLevelQtyKey = null;
            /** @var InventoryLevel $inventoryLevel */
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $invLevelQtyKey = sprintf('%s_|_%s', $sku, $inventoryLevel->getId());
                $invLevelQty = $inventoryLevel->getVirtualInventoryQty();
                if (isset($productsQty[$invLevelQtyKey])) {
                    $invLevelQty = $invLevelQty - $productsQty[$invLevelQtyKey];
                }
                $warehouse = $inventoryLevel->getWarehouse();
                $invLevToWh[$warehouse->getId()] = $inventoryLevel;
                if ($invLevelQty >= $orderItem->getQuantity() ||
                    $this->isWarehouseEligible($orderItem, $inventoryLevel)
                ) {
                    $productsByWh[$key][] = $warehouse;
                }
            }
            if (array_key_exists($key, $productsByWh) && is_array($productsByWh[$key])) {
                usort($productsByWh[$key], function (Warehouse $a, Warehouse $b) use ($product) {
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
                            $preferedSupplier = $this->getPreferredSupplierWhichCanDropship($product);
                            if ($preferedSupplier) {
                                $supplierWarehouseCode = sprintf(
                                    '%s_external_warehouse',
                                    str_replace(' ', '_', strtolower($preferedSupplier->getName()))
                                );
                                if ($aWhCode === $supplierWarehouseCode) {
                                    return -1;
                                } elseif ($bWhCode === $supplierWarehouseCode) {
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
            }
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

    /**
     * @param OrderItem $orderItem
     * @param InventoryLevel $inventoryLevel
     * @return bool
     */
    protected function isWarehouseEligible(OrderItem $orderItem, InventoryLevel $inventoryLevel)
    {
        // these are basically rules, we might need to convert this into some rule based system
        // in order to have some more control over the priority of the conditions of the item
        $warehouse = $inventoryLevel->getWarehouse();
        $warehouseType = $warehouse->getWarehouseType()->getName();
        $inventoryItem = $inventoryLevel->getInventoryItem();
        if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
            return true;
        }

        if ($this->estimation === true && $inventoryItem->isOrderOnDemandAllowed()) {
            return true;
        }

        if ($this->estimation === true && $inventoryItem->isCanPreorder() &&
            $inventoryItem->getMaxQtyToPreorder() >= $orderItem->getQuantity()
        ){
            return true;
        }

        if ($this->estimation === true && $inventoryItem->isBackorderAllowed() &&
            $inventoryItem->getMaxQtyToBackorder() >= $orderItem->getQuantity()
        ){
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @return Supplier|null
     */
    protected function getPreferredSupplierWhichCanDropship(Product $product)
    {
        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier && $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority  &&
                $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        return $preferredSupplier;
    }
}
