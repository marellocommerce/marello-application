<?php

namespace Marello\Bundle\TaskBundle\EventListener\Datagrid;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class TaskGridListener
{
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $datasource = $datagrid->getDatasource();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $datasource->getQueryBuilder();

        $joins = $queryBuilder->getDQLPart('join');
        $queryBuilder->resetDQLPart('join');
        $alias = $queryBuilder->getRootAliases()[0];

        /** @var Join $join */
        foreach ($joins[$alias] as $join) {
            $method = $join->getJoinType() === Join::INNER_JOIN ? 'innerJoin' : 'leftJoin';
            if ($join->getJoin() === $alias . '.owner') {
                $method = 'leftJoin';
            }
            $queryBuilder->$method(
                $join->getJoin(),
                $join->getAlias(),
                $join->getConditionType(),
                $join->getCondition(),
                $join->getIndexBy()
            );
        }
    }
}
