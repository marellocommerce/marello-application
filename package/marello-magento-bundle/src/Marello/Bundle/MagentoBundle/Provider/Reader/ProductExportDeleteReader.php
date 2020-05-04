<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductExportDeleteReader extends AbstractExportReader
{
    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->andWhere('o.sku' . ' = :sku')
            ->setParameter('sku', $this->productSku);

        return $qb;
    }
}
