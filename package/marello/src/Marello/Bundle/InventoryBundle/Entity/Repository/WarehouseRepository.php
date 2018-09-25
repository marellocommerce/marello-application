<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseRepository extends EntityRepository
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
     * Finds default warehouse.
     *
     * @return Warehouse
     */
    public function getDefault()
    {
        $qb = $this->createQueryBuilder('wh');
        $qb
            ->where($qb->expr()->eq('wh.default', $qb->expr()->literal(true)));

        return $this->aclHelper->apply($qb)->getSingleResult();
    }
}
