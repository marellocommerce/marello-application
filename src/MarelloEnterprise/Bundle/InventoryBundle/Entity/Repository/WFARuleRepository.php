<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;

class WFARuleRepository extends ServiceEntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getUsedStrategies(AclHelper $aclHelper)
    {
        $qb = $this
            ->createQueryBuilder('wfa')
            ->distinct(true)
            ->select('wfa.strategy');

        return $aclHelper->apply($qb)->getArrayResult();
    }


    /**
     * @param AclHelper $aclHelper
     * @return WFARule[]
     */
    public function findAllWFARules(AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('wfa');

        return $aclHelper->apply($qb)->getResult();
    }
}
