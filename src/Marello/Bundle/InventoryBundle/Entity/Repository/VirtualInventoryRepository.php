<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;

class VirtualInventoryRepository extends EntityRepository
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
     * Finds level based on Product and SalesChannelGroup
     *
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @return VirtualInventoryLevel
     */
    public function findExistingVirtualInventory(ProductInterface $product, SalesChannelGroup $group)
    {
        $qb = $this->createQueryBuilder('virtual_inventory');
        $qb
            ->where(
                $qb->expr()->eq('virtual_inventory.salesChannelGroup', ':salesChannelGroup'),
                $qb->expr()->eq('virtual_inventory.product', ':product')
            )
            ->setParameter('product', $product)
            ->setParameter('salesChannelGroup', $group);

        return $this->aclHelper->apply($qb)->getOneOrNullResult();
    }
}
