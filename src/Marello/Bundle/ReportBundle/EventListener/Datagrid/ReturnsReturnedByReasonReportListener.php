<?php

namespace Marello\Bundle\ReportBundle\EventListener\Datagrid;

use Marello\Bundle\ReturnBundle\Entity\Repository\ReturnItemRepository;
use Oro\Bundle\DataGridBundle\Datasource\ArrayDatasource\ArrayDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Exception\UnexpectedTypeException;

class ReturnsReturnedByReasonReportListener
{
    /**
     * @var ReturnItemRepository
     */
    private $returnItemRepository;
    
    public function __construct(ReturnItemRepository $returnItemRepository)
    {
        $this->returnItemRepository = $returnItemRepository;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $datasource = $datagrid->getDatasource();

        if (!$datasource instanceof ArrayDatasource) {
            throw new UnexpectedTypeException($datasource, ArrayDatasource::class);
        }

        $source = $this->returnItemRepository->getReturnQuantityByReason();

        $datasource->setArraySource($source);
    }
}
