<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelTypeRepository extends EntityRepository
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
     * @param string $query
     *
     * @return SalesChannelType[]
     */
    public function search($query)
    {
        $qb = $this->createQueryBuilder('sct');
        $qb
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like('sct.name', ':query'),
                    $qb->expr()->like('sct.label', ':query')
                )
            )
            ->setParameter('query', '%' . $query . '%');
        if ($this->aclHelper) {
            return $this->aclHelper->apply($qb)->getResult();
        }

        return $qb->getQuery()->getResult();
    }
}
