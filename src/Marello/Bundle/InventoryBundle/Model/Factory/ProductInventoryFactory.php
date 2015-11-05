<?php

namespace Marello\Bundle\InventoryBundle\Model\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Model\ProductInventory;
use Marello\Bundle\InventoryBundle\Model\WarehouseInventory;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductInventoryFactory
{
    /** @var Registry */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Creates product inventory model. This model represents product inventory for all warehouses.
     *
     * @param Product $product
     *
     * @return ProductInventory
     */
    public function getProductInventory(Product $product)
    {
        $warehouses     = $this->doctrine->getRepository('MarelloInventoryBundle:Warehouse')
            ->allIndexed();
        $inventoryItems = $this->doctrine->getRepository('MarelloInventoryBundle:InventoryItem')
            ->findByProductIndexByWarehouse($product);

        $productInventory = new ProductInventory($product);

        /*
         * For each warehouse configured in system ...
         * warehouse inventory model is created and added to product inventory model.
         */
        foreach ($warehouses as $id => $warehouse) {
            if (array_key_exists($id, $inventoryItems)) {
                /*
                 * If there is inventory item present for given warehouse ...
                 * Create a new warehouse inventory model based on that inventory item.
                 */
                $productInventory->getWarehouses()->add(
                    WarehouseInventory::fromInventoryItem($inventoryItems[$id])
                );
            } else {
                /*
                 * If there is no inventory item present for given warehouse ...
                 * Create a new warehouse inventory model based on warehouse and product.
                 */
                $productInventory->getWarehouses()->add(
                    WarehouseInventory::fromWarehouseAndProduct($warehouse, $product)
                );
            }
        }

        return $productInventory;
    }
}
