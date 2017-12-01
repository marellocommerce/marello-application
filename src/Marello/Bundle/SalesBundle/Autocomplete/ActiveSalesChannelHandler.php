<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Doctrine\ORM\QueryBuilder;

class ActiveSalesChannelHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $entityIds = explode(',', $query);

        $queryBuilder = $this->getBasicQueryBuilder();
        $queryBuilder->andWhere($queryBuilder->expr()->in('sc.id', $entityIds));

        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $queryBuilder = $this->getBasicQueryBuilder();
        if ($search) {
            $this->addSearchCriteria($queryBuilder, $search);
        }
        $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $search
     */
    protected function addSearchCriteria(QueryBuilder $queryBuilder, $search)
    {
        $conditions = [];
        foreach ($this->getProperties() as $property) {
            $conditions[] = $queryBuilder->expr()->like(sprintf('sc.%s', $property), ':search');
        }
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX()->addMultiple($conditions)
            )
            ->setParameter('search', '%' . str_replace(' ', '%', $search) . '%');
    }

    /**
     * @return QueryBuilder
     */
    protected function getBasicQueryBuilder()
    {
        $qb = $this->entityRepository->createQueryBuilder('sc');

        return $qb
            ->where($qb->expr()->eq('sc.active', $qb->expr()->literal(true)))
            ->orderBy('sc.name', 'ASC');
    }
}
