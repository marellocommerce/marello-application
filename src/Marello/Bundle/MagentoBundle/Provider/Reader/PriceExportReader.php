<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class PriceExportReader extends AbstractExportReader
{
    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', '_product.channels'),
                $qb->expr()->eq("o.currency", ":currency")
            )
            ->setParameters([
                'salesChannel' => $this->getSalesChannel(),
                'currency' => $this->getSalesChannel()->getCurrency()
            ]);

        return $qb;
    }
}
