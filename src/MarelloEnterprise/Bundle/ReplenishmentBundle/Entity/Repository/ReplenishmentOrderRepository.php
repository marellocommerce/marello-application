<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ReplenishmentOrderRepository extends EntityRepository
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
     * @param int $replOrderConfig
     *
     * @return ReplenishmentOrder[]
     */
    public function findByConfig($replOrderConfig)
    {
        $qb = $this->createQueryBuilder('ro');
        $qb
            ->where(
                $qb->expr()->eq(':replOrderConfig', 'ro.replOrderConfig')
            )
            ->setParameter('replOrderConfig', $replOrderConfig);

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }
}
