<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class WarehouseRepository extends EntityRepository
{
    public function allIndexed()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('w')
            ->from('MarelloInventoryBundle:Warehouse', 'w', 'w.id')
            ->orderBy('w.id');

        return $qb->getQuery()->execute();
    }
}
