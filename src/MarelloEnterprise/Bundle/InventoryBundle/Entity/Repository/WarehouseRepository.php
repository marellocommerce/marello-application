<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper) // weedizp3
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * @param int $id
     * @return Warehouse[]
     */
    public function getDefaultExcept($id)
    {
        $qb = $this->createQueryBuilder('wh');
        $qb
            ->where($qb->expr()->eq('wh.default', $qb->expr()->literal(true)))
            ->andWhere($qb->expr()->not($qb->expr()->eq('wh.id', ':id')))
            ->setParameter(':id', $id);

        return $this->aclHelper->apply($qb)->getResult();
    }
}
