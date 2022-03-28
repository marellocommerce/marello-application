<?php

namespace Marello\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CategoryRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper) // weedizp3
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param array $relatedCategoriesIds
     * @return array
     */
    public function findExcludedCategoriesIds(array $relatedCategoriesIds)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.id NOT IN(:categories)')
            ->setParameter('categories', $relatedCategoriesIds);

        return $this->aclHelper->apply($qb)->getArrayResult();
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
