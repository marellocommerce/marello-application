<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Doctrine\ORM\Query\Expr\Join;
use Marello\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;

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
