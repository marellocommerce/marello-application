<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;

class SystemGroupGlobalWarehousesSearchHandler extends SearchHandler
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
            $conditions[] = $queryBuilder->expr()->like(sprintf('wh.%s', $property), ':search');
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
        $qb = $this->entityRepository->createQueryBuilder('wh');
        $qb
            ->innerJoin('wh.warehouseType', 'wht')
            ->innerJoin('wh.group', 'whg')
            ->orderBy('wh.label', 'ASC');
        if (!$groupId) {
            $qb
                ->where('whg.system = :isSystem AND wht.name in (:types)')
                ->setParameter('isSystem', true)
                ->setParameter('types', [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL
                ]);
        } else {
            $qb
                ->where('(whg.system = :isSystem AND wht.name in (:types)) OR whg.id = :id')
                ->setParameter('isSystem', true)
                ->setParameter('types', [
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                    WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL
                ])
                ->setParameter('id', $groupId);
        }
        return $qb;
    }
}
