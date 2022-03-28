<?php

namespace Marello\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CompanyRepository extends EntityRepository
{
    /**
     * @param string $name
     *
     * @return null|Company
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param int $companyId
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getChildrenIds($companyId, AclHelper $aclHelper = null) // weedizp3
    {
        $qb = $this->createQueryBuilder('company');
        $qb->select('company.id as company_id')
            ->where($qb->expr()->eq('IDENTITY(company.parent)', ':parent'))
            ->setParameter('parent', $companyId);

        if ($aclHelper) {
            $query = $aclHelper->apply($qb);
        } else {
            $query = $qb->getQuery();
        }

        $result = array_map(
            function ($item) {
                return $item['company_id'];
            },
            $query->getArrayResult()
        );
        $children = $result;

        if ($result) {
            foreach ($result as $childId) {
                $children = array_merge($children, $this->getChildrenIds($childId, $aclHelper));
            }
        }

        return $children;
    }
}
