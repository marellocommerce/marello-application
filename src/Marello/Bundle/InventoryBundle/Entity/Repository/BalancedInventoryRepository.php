<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class BalancedInventoryRepository extends EntityRepository
{
    public function findExistingBalancedInventory(
        ProductInterface $product,
        SalesChannelGroup $group,
        AclHelper $aclHelper
    ): ?BalancedInventoryLevel {
        $qb = $this->createQueryBuilder('balanced_inventory');
        $qb
            ->where(
                $qb->expr()->eq('balanced_inventory.salesChannelGroup', ':salesChannelGroup'),
                $qb->expr()->eq('balanced_inventory.product', ':product')
            )
            ->setParameter('product', $product)
            ->setParameter('salesChannelGroup', $group);

        return $aclHelper->apply($qb)->getOneOrNullResult();
    }
}
