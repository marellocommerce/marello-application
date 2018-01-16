<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseGroupRepository extends EntityRepository
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
     * @return WarehouseGroup
     */
    public function findSystemWarehouseGroup()
    {
        $qb = $this->createQueryBuilder('whg');
        $qb
            ->where($qb->expr()->eq('whg.system', $qb->expr()->literal(true)));
        $results = $this->aclHelper->apply($qb)->getResult();
        
        return reset($results);
    }
}
