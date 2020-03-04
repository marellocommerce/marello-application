<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class NotLinkedWarehouseGroupsSearchHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $entityIds = explode(',', $query);

        $queryBuilder = $this->getBasicQueryBuilder();
        $queryBuilder->andWhere($queryBuilder->expr()->in('whg.id', $entityIds));

        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }

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
            $conditions[] = $queryBuilder->expr()->like(sprintf('whg.%s', $property), ':search');
        }
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX()->addMultiple($conditions)
            )
            ->setParameter('search', '%' . str_replace(' ', '%', $search) . '%');
    }

    /**
     * @param int $linkId
     * @return QueryBuilder
     */
    protected function getBasicQueryBuilder($linkId = null)
    {
        $qb = $this->entityRepository->createQueryBuilder('whg');

        $qb
            ->leftJoin('whg.warehouseChannelGroupLink', 'wcgl')
            ->orderBy('whg.name', 'ASC')
            ->andWhere('wcgl.id IS NULL')
            ->andWhere('whg.system != true');

        if ($linkId) {
            $qb
                ->orWhere('wcgl.id = :id')
                ->setParameter('id', $linkId);
        }

        return $qb;
    }
}
