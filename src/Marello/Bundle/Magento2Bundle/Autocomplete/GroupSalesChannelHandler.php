<?php

namespace Marello\Bundle\Magento2Bundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\SalesBundle\Autocomplete\GroupSalesChannelHandler as BaseHandler;

class GroupSalesChannelHandler extends BaseHandler
{
    /**
     * @param string $searchTerm
     * @param int    $firstResult
     * @param int    $maxResults
     * @return QueryBuilder
     */
    protected function prepareQueryBuilder($searchTerm, $firstResult, $maxResults)
    {
        $queryBuilder = parent::prepareQueryBuilder($searchTerm, $firstResult, $maxResults);

        $queryBuilder->andWhere($queryBuilder->expr()->isNull('scg.integrationChannel'));

        return $queryBuilder;
    }
}
