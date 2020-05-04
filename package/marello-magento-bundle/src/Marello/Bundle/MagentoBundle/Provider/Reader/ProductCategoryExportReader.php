<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Doctrine\ORM\Query\Expr\Join;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;

class ProductCategoryExportReader extends EntityReader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb->join(
            'MarelloMagentoBundle:Category',
            "magentoCategory",
            Join::WITH,
            $qb->expr()->andX(
                $qb->expr()->eq('magentoCategory.code', 'o.code')
            )
        );

        return $qb;
    }
}
