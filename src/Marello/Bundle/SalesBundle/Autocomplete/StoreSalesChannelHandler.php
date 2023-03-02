<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Marello\Bundle\SalesBundle\Migrations\Data\ORM\LoadSalesChannelTypesData;
use Doctrine\ORM\QueryBuilder;

class StoreSalesChannelHandler extends AbstractMultiConditionSalesChannelHandler
{
    protected function getBasicQueryBuilder(): QueryBuilder
    {
        $qb = $this->entityRepository->createQueryBuilder('sc');

        return $qb
            ->where($qb->expr()->eq('sc.channelType', $qb->expr()->literal(LoadSalesChannelTypesData::STORE)))
            ->orderBy('sc.name', 'ASC');
    }
}
