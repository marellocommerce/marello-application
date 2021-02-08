<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class GroupSalesChannelHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function checkAllDependenciesInjected()
    {
        if (!$this->entityRepository || !$this->idFieldName) {
            throw new \RuntimeException('Search handler is not fully configured');
        }
    }

    /**
     * @param string $searchTerm
     * @param int $firstResult
     * @param int $maxResults
     * @return QueryBuilder
     */
    protected function prepareQueryBuilder($searchTerm, $firstResult, $maxResults)
    {
        $queryBuilder = $this->entityRepository->getNonSystemSalesChannelBySearchTermQB($searchTerm);
        $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        return $queryBuilder;
    }

    /**
     * {@inheritDoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $queryBuilder = $this->prepareQueryBuilder($search, $firstResult, $maxResults);
        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }
}
