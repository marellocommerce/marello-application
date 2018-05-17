<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class InventoryLevelExportReader extends EntityReader
{
    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->innerJoin('o.product', 'p')
            ->innerJoin('o.salesChannelGroup', 'g');
//            ->andWhere('p.id = :productId')
//            ->andWhere('g.id = :groupId')
//            ->setParameter('productId', $this->productId ? : -1)
//            ->setParameter('groupId', $this->groupId ? : -1);


        //TODO: filter based on channel group id

        return $qb;
    }
}
