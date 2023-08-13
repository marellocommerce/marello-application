<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class ExpectedInventoryItemGridListener
{
    public function onBuildAfter(BuildAfter $event)
    {
        $grid = $event->getDatagrid();
        $productId = $this->getParameter($grid, 'productId');
        /** @var QueryBuilder $qb */
        $qb = $grid->getDatasource()->getQueryBuilder();
        $rootAlias = $qb->getRootAliases()[0];
        $qb
            ->andWhere($qb->expr()->eq($rootAlias . '.product', ':productId'))
            ->setParameter('productId', $productId);
    }

    protected function getParameter(DatagridInterface $datagrid, string $parameterName)
    {
        $value = $datagrid->getParameters()->get($parameterName);

        if ($value === null) {
            throw new \LogicException(sprintf('Parameter "%s" must be set', $parameterName));
        }

        return $value;
    }
}
