<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use \Doctrine\DBAL\Types\Type;

class ReplenishmentOrderConfigRepository extends EntityRepository
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
     * @param \DateTime $datetime
     *
     * @return ReplenishmentOrderConfig[]
     */
    public function findNotExecuted(\DateTime $datetime = null)
    {
        if (!$datetime) {
            $datetime = new \DateTime();
        }
        
        $qb = $this->createQueryBuilder('roc');
        $qb
            ->andWhere($qb->expr()->eq(':executed', 'roc.executed'))
            ->andWhere($qb->expr()->gte(':dt', 'roc.executionDateTime'))
            ->setParameter('executed', false, Type::BOOLEAN)
            ->setParameter('dt', $datetime, Type::DATETIME);

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }
}
