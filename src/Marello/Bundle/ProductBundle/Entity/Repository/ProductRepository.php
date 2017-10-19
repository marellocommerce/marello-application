<?php

namespace Marello\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ProductRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * Return products for specified price list and product IDs
     *
     * @param int $salesChannel
     * @param array $productIds
     *
     * @return Product[]
     */
    public function findBySalesChannel($salesChannel, array $productIds)
    {
        if (!$productIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('product');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'product.channels'),
                $qb->expr()->in('product.id', ':productIds')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productIds', $productIds);

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }
}
