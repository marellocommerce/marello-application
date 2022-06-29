<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Strategy\WFA\Quantity\Calculator;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

abstract class AbstractWHCalculatorTest extends TestCase
{
    use EntityTrait;

    /**
     * @var Warehouse[]
     */
    protected $warehouses = [];

    /**
     * @var Supplier[]
     */
    protected $suppliers = [];
    
    /**
     * @return Supplier[]
     */
    protected function getSuppliers()
    {
        if (empty($this->suppliers)) {
            for ($i = 1; $i <= 6; $i++) {
                $this->suppliers[$i] = $this->getEntity(
                    Supplier::class,
                    ['name' => sprintf('Supplier%s', $i), 'canDropship' => true]
                );
            }
        }

        return $this->suppliers;
    }

    /**
     * @return Warehouse[]
     */
    protected function getWarehouses()
    {
        if (empty($this->warehouses)) {
            $globalWarehouseType = $this->getEntity(
                WarehouseType::class,
                [],
                [WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL]
            );
            $externalWarehouseType = $this->getEntity(
                WarehouseType::class,
                [],
                [WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL]
            );

            $this->warehouses[1] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 1,
                    'default' => true,
                    'code' => 'default_warehouse',
                    'warehouseType' => $globalWarehouseType
                ]
            );
            $this->warehouses[2] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 2,
                    'default' => false,
                    'code' => 'not_default_warehouse',
                    'warehouseType' => $globalWarehouseType
                ]
            );
            $this->warehouses[3] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 3,
                    'default' => false,
                    'code' => 'supplier3_external_warehouse',
                    'warehouseType' => $externalWarehouseType
                ]
            );
            $this->warehouses[4] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 4,
                    'default' => false,
                    'code' => 'supplier4_external_warehouse',
                    'warehouseType' => $externalWarehouseType
                ]
            );
            $this->warehouses[5] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 5,
                    'default' => false,
                    'code' => 'supplier5_external_warehouse',
                    'warehouseType' => $externalWarehouseType
                ]
            );
            $this->warehouses[6] = $this->getEntity(
                Warehouse::class,
                [
                    'id' => 6,
                    'default' => false,
                    'code' => 'supplier6_external_warehouse',
                    'warehouseType' => $externalWarehouseType
                ]
            );
        }

        return $this->warehouses;
    }

    /**
     * @param int $id
     * @param array $quantities
     * @param array $priorities
     * @return Product
     */
    protected function mockProduct($id, array $quantities, array $priorities)
    {
        $warehouses = $this->getWarehouses();
        $suppliers = $this->getSuppliers();
        /** @var Product $product */
        $product = $this->getEntity(Product::class, ['sku' => sprintf('TPD000%s', $id)]);
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->getEntity(InventoryItem::class, ['id' => $id], [$product]);
        foreach ($quantities as $k => $quantity) {
            /** @var InventoryLevel $inventoryLevel */
            $inventoryLevel = $this->getEntity(
                InventoryLevel::class,
                [
                    'id' => $k,
                    'inventoryItem' => $inventoryItem,
                    'warehouse' => $warehouses[$k],
                    'inventory' => $quantity
                ]
            );
            $inventoryItem->addInventoryLevel($inventoryLevel);
        }
        foreach ($priorities as $key => $priority) {
            /** @var ProductSupplierRelation $productSupplierRelation */
            $productSupplierRelation = $this->getEntity(
                ProductSupplierRelation::class,
                [
                    'id' => $key,
                    'product' => $product,
                    'supplier' => $suppliers[$key],
                    'priority' => $priority,
                    'canDropship' => $suppliers[$key]->getCanDropship()
                ]
            );
            $product->addSupplier($productSupplierRelation);
        }

        return $product;
    }
}
