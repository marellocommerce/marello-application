<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseRepository extends EntityRepository
{
    /**
     * @return Warehouse[]
     */
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
