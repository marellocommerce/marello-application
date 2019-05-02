<?php

namespace Marello\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CategoryRepository extends EntityRepository
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
     * @param array $relatedCategoriesIds
     * @return array
     */
    public function findExcludedCategoriesIds(array $relatedCategoriesIds)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.id NOT IN(:categories)')
            ->setParameter('categories', $relatedCategoriesIds);

        return $this->aclHelper->apply($qb)->getArrayResult();
    }
}
