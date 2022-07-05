<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class InventoryLevelWarehousesSearchHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $queryBuilder = $this->getBasicQueryBuilder();
        $this->addSearchCriteria($queryBuilder, $search);
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
            $conditions[] = $queryBuilder->expr()->like(sprintf('wh.%s', $property), ':search');
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
        $qb = $this->entityRepository->createQueryBuilder('wh');
        $qb
            ->innerJoin('wh.warehouseType', 'wht')
            ->orderBy('wh.label', 'ASC');
        $qb
            ->where('wht.name in (:types)')
            ->setParameter('types', [
                WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED,
                WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL,
            ]);
        return $qb;
    }
}
