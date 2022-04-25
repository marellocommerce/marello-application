<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseGroupRepository extends ServiceEntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @return WarehouseGroup
     */
    public function findSystemWarehouseGroup(AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('whg');
        $qb
            ->where($qb->expr()->eq('whg.system', $qb->expr()->literal(true)));
        $results = $aclHelper->apply($qb)->getResult();
        
        return reset($results);
    }
}
