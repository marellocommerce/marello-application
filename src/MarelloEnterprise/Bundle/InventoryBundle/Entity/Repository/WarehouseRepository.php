<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseRepository extends ServiceEntityRepository
{
    /**
     * @param int $id
     * @param AclHelper $aclHelper
     * @return Warehouse[]
     */
    public function getDefaultExcept($id, AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('wh');
        $qb
            ->where($qb->expr()->eq('wh.default', $qb->expr()->literal(true)))
            ->andWhere($qb->expr()->not($qb->expr()->eq('wh.id', ':id')))
            ->setParameter(':id', $id);

        return $aclHelper->apply($qb)->getResult();
    }
}
