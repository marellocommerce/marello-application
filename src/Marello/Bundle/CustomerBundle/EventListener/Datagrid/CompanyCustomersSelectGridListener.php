<?php

namespace Marello\Bundle\CustomerBundle\EventListener\Datagrid;

use Marello\Bundle\CustomerBundle\Entity\Repository\CompanyRepository;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;

class CompanyCustomersSelectGridListener
{
    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @param CompanyRepository $companyRepository
     */
    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }
    
    /**
     * @param OrmResultBeforeQuery $event
     */
    public function onResultBeforeQuery(OrmResultBeforeQuery $event)
    {
        $grid = $event->getDatagrid();
        $params = $grid->getParameters();
        $qb = $event->getQueryBuilder();
        if ($params->has('companyId') && $params->get('companyId')) {
            $companyId = (int)$params->get('companyId');
            $childrenIds = $this->companyRepository->getChildrenIds($companyId);
            $childrenIds[] = $companyId;
            $qb
                ->andWhere('c.company IN (:companyIds)')
                ->setParameter('companyIds', $childrenIds);
        }
    }
}
