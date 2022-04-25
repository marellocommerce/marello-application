<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemRepository extends ServiceEntityRepository
{
    /**
     * @param Product $product
     * @return InventoryItem
     */
    public function findOneByProduct(Product $product)
    {
        return $this->findOneBy(['product' => $product]);
    }
}
