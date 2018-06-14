<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductExportReader extends AbstractExportReader
{
    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);


        if ($this->productSku) {
            $qb
                ->andWhere('o.sku' . ' = :sku')
                ->setParameter('sku', $this->productSku);
        } else {
            $qb
                ->where(
                    $qb->expr()->isMemberOf(':salesChannel', 'o.channels')
                )
                ->setParameter('salesChannel', $this->getSalesChannel());
        }

        return $qb;
    }
}
