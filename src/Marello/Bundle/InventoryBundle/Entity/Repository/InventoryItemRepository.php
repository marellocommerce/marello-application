<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryItemRepository extends EntityRepository
{
    /**
     * @param Product $product
     *
     * @return mixed
     */
    public function findByProductIndexByWarehouse(Product $product)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('i')
            ->from('MarelloInventoryBundle:InventoryItem', 'i')
            ->leftJoin('i.warehouse', 'wh')
            ->where($qb->expr()->eq('i.product', $product->getId()));

        /** @var InventoryItem[] $items */
        $items = $qb->getQuery()->execute();
        $out = [];

        foreach ($items as $item) {
            $out[$item->getWarehouse()->getId()] = $item;
        }

        return $out;
    }
}
