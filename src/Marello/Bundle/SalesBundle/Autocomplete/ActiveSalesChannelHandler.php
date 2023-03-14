<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;

class ActiveSalesChannelHandler extends AbstractMultiConditionSalesChannelHandler
{
    protected function getBasicQueryBuilder(): QueryBuilder
    {
        $qb = $this->entityRepository->createQueryBuilder('sc');

        return $qb
            ->where($qb->expr()->eq('sc.active', $qb->expr()->literal(true)))
            ->orderBy('sc.name', 'ASC');
    }
}
