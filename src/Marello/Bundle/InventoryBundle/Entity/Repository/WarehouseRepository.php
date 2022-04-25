<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseRepository extends ServiceEntityRepository
{
    public function getDefault(AclHelper $aclHelper): Warehouse
    {
        $qb = $this->createQueryBuilder('wh');
        $qb
            ->where($qb->expr()->eq('wh.default', $qb->expr()->literal(true)));

        return $aclHelper->apply($qb)->getSingleResult();
    }
}
