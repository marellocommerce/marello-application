<?php

namespace Marello\Bundle\ProductBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Doctrine\ORM\QueryBuilder;

class SalesChannelProductHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $entityIds = explode(',', $parts[0]);
        $channelId = !empty($parts[1]) ? $parts[1] : false;

        $queryBuilder = $this->getBasicQueryBuilder($channelId);
        $queryBuilder->andWhere($queryBuilder->expr()->in('p.id', $entityIds));

        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $parts = explode(';', $search);
        $searchString = $parts[0];
        $channelId = !empty($parts[1]) ? $parts[1] : false;
        
        $queryBuilder = $this->getBasicQueryBuilder($channelId);
        if ($searchString) {
            $this->addSearchCriteria($queryBuilder, $searchString);
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
            $conditions[] = $queryBuilder->expr()->like(sprintf('p.%s', $property), ':search');
        }
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX()->addMultiple($conditions)
            )
            ->setParameter('search', '%' . str_replace(' ', '%', $search) . '%');
    }

    /**
     * @param int $channelId
     * @return QueryBuilder
     */
    protected function getBasicQueryBuilder($channelId)
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('p');
        $queryBuilder->join('p.channels', 'sc')
            ->andWhere('sc.id = :channel_id')
            ->setParameter('channel_id', (int) $channelId);

        return $queryBuilder;
    }
}
