<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseRepository extends EntityRepository
{
    public function getDefault(AclHelper $aclHelper): Warehouse
    {
        $qb = $this->createQueryBuilder('wh');
        $qb
            ->where($qb->expr()->eq('wh.default', $qb->expr()->literal(true)));

        return $aclHelper->apply($qb)->getSingleResult();
    }
}
