<?php

namespace Marello\Bundle\CustomerBundle\Autocomplete;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\FormBundle\Autocomplete\FullNameSearchHandler;
use Doctrine\ORM\QueryBuilder;

class CompanyCustomerHandler extends FullNameSearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $entityIds = explode(',', $parts[0]);
        $companyId = !empty($parts[1]) ? $parts[1] : null;

        $queryBuilder = $this->getBasicQueryBuilder($companyId);
        $queryBuilder->andWhere($queryBuilder->expr()->in('c.id', $entityIds));

        return $this->aclHelper->apply($queryBuilder->getQuery())->getResult();
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        $parts = explode(';', $search);
        $searchString = $parts[0];
        $companyId = !empty($parts[1]) ? $parts[1] : null;
        
        $queryBuilder = $this->getBasicQueryBuilder($companyId);
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
            $conditions[] = $queryBuilder->expr()->like(sprintf('c.%s', $property), ':search');
        }
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX()->addMultiple($conditions)
            )
            ->setParameter('search', '%' . str_replace(' ', '%', $search) . '%');
    }

    /**
     * @param int|null $companyId
     * @return QueryBuilder
     */
    protected function getBasicQueryBuilder($companyId = null)
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('c');
        if ($companyId) {
            $ids = $this->objectManager->getRepository(Company::class)->getChildrenIds($companyId);
            $ids[] = (int)$companyId;
            $queryBuilder
                ->andWhere('c.company IN (:company_ids)')
                ->setParameter('company_ids', $ids);
        }

        return $queryBuilder;
    }
}
