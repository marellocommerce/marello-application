<?php

namespace Marello\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CategoryRepository extends ServiceEntityRepository
{
    /**
     * @param array $relatedCategoriesIds
     * @param AclHelper $aclHelper
     * @return array
     */
    public function findExcludedCategoriesIds(array $relatedCategoriesIds, AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.id NOT IN(:categories)')
            ->setParameter('categories', $relatedCategoriesIds);

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param Product $product
     * @return Category[]
     */
    public function findByProduct(Product $product)
    {
        $qb = $this->createQueryBuilder('c')
            ->where(':product MEMBER OF c.products')
            ->setParameters(['product' => $product]);

        return $qb->getQuery()->getResult();
    }
}
