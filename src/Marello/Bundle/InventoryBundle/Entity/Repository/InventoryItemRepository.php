<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryItemRepository extends EntityRepository
{
    /**
     * @param Warehouse $warehouse
     * @param Product   $product
     *
     * @return null|InventoryItem
     */
    public function findOneByWarehouseAndProduct(Warehouse $warehouse, Product $product)
    {
        return $this->findOneBy(compact('warehouse', 'product'));
    }

    /**
     * @param Warehouse $warehouse
     * @param Product   $product
     * @param bool      $persistNew
     *
     * @return InventoryItem|null
     */
    public function findOrCreateByWarehouseAndProduct(Warehouse $warehouse, Product $product, $persistNew = false)
    {
        $inventoryItem = $this->findOneByWarehouseAndProduct($warehouse, $product);

        if (!$inventoryItem) {
            $inventoryItem = new InventoryItem();
            $inventoryItem
                ->setWarehouse($warehouse)
                ->setProduct($product);

            if ($persistNew) {
                $this->getEntityManager()->persist($inventoryItem);
            }
        }

        return $inventoryItem;
    }
}
