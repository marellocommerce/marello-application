<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Doctrine\ORM\Query\Expr\Join;
use Marello\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;

class InventoryLevelExportReader extends AbstractExportReader
{
    /**
     * @param $code
     * @return mixed
     */
    protected function getContextOption($code)
    {
        $context = $this->contextRegistry
            ->getByStepExecution($this->stepExecution);

        return $context->getValue($code);
    }

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb->innerJoin(
            'MarelloSalesBundle:SalesChannel',
            "sc",
            Join::WITH,
            $qb->expr()->andX(
                $qb->expr()->eq('sc.group', '_salesChannelGroup.id'),
                $qb->expr()->eq("sc.integrationChannel", ":integrationChannel")
            )
        );

        $qb->setParameter("integrationChannel", $this->integrationChannelId);

        return $qb;
    }
}
