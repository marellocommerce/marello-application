<?php

namespace Marello\Bundle\SalesBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class SystemGroupSalesChannelHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $query = explode(';', $search);
        if (isset($query[1])) {
            $queryBuilder = $this->getBasicQueryBuilder($query[1]);
        } else {
            $queryBuilder = $this->getBasicQueryBuilder();
        }
        if (!empty($query[0])) {
            $this->addSearchCriteria($queryBuilder, $query[0]);
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
     * @param int $groupId
     * @return QueryBuilder
     */
    protected function getBasicQueryBuilder($groupId = null)
    {
        $qb = $this->entityRepository->createQueryBuilder('sc');
        $qb
            ->innerJoin('sc.group', 'scg')
            ->orderBy('sc.name', 'ASC');
        if (!$groupId) {
            $qb->where($qb->expr()->eq('scg.system', $qb->expr()->literal(true)));
        } else {
            $qb
                ->where('scg.system = :isSystem OR scg.id = :id')
                ->setParameter('isSystem', true)
                ->setParameter('id', $groupId);
        }
        return $qb;
    }
}
