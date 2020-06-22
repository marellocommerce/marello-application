<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

/**
 * @todo Cover with functional test
 */
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
     * @param int    $firstResult
     * @param int    $maxResults
     * @return QueryBuilder
     */
    protected function prepareQueryBuilder($searchTerm, $firstResult, $maxResults)
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('scg');
        $queryBuilder
            ->where($queryBuilder->expr()->like('LOWER(scg.name)', ':searchTerm'))
            ->andWhere(($queryBuilder->expr()->eq('scg.system', $queryBuilder->expr()->literal(false))))
            ->setParameter('searchTerm', '%' . mb_strtolower($searchTerm) . '%')
            ->orderBy('scg.name', 'ASC')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $queryBuilder = $this->prepareQueryBuilder($search, $firstResult, $maxResults);
        $query = $this->aclHelper->apply($queryBuilder, 'VIEW');

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $id = $parts[0];

        $criteria = [$this->idFieldName => $id];

        return [$this->entityRepository->findOneBy($criteria, null)];
    }
}
