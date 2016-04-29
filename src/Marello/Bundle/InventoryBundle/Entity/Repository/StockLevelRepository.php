<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\StockModify;
use Marello\Bundle\ProductBundle\Entity\Product;

class StockLevelRepository extends EntityRepository
{
    /**
     * @param Product                                           $product
     * @param Warehouse                                         $warehouse
     * @param \Marello\Bundle\InventoryBundle\Model\StockModify $modify
     */
    public function modify(Product $product, Warehouse $warehouse, StockModify $modify)
    {
        $item = $this->getEntityManager()->getRepository(InventoryItem::class)
            ->findOneBy(compact('product', 'warehouse'));

        if (!$item) {
            $item = new InventoryItem($product, $warehouse);
        }

        $this->modifyItem($item, $modify);

        $this->getEntityManager()->persist($item);
    }

    /**
     * @param InventoryItem                                     $inventoryItem
     * @param \Marello\Bundle\InventoryBundle\Model\StockModify $modify
     */
    public function modifyItem(InventoryItem $inventoryItem, StockModify $modify)
    {
        $newLevel = $modify->toStockLevel($inventoryItem);

        $this->getEntityManager()->persist($newLevel);
    }
}
