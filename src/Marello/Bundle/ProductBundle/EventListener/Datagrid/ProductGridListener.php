<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr\GroupBy;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class ProductGridListener
{
    /**
     *
     * @param BuildAfter $event
     */
    public function buildAfter(BuildAfter $event)
    {
        $grid = $event->getDatagrid();
        /** @var OrmDatasource $dataSource */
        $dataSource = $grid->getDatasource();
        /** @var GroupBy[] $groupByParts */
        $groupByParts = $dataSource->getQueryBuilder()->getDQLPart('groupBy');
        $newGroupByParts = [];
        foreach ($groupByParts as $key => $groupByPart) {
            $parts = [];
            foreach ($groupByPart->getParts() as $k => $part) {
                $part = str_replace('hasImage', '',$part);
                $part = str_replace(',,', ',',$part);
                $parts[$k] = $part;
            }
            $newGroupByParts[$key] = new GroupBy($parts);
        }
        $dataSource->getQueryBuilder()->resetDQLPart('groupBy');
        $dataSource->getQueryBuilder()->add('groupBy', $newGroupByParts);
    }
}
