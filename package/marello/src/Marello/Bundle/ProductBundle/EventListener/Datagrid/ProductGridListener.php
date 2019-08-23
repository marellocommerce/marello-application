<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\GroupBy;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;

use Marello\Bundle\ProductBundle\Datagrid\ORM\Query\ProductsGridSqlWalker;

class ProductGridListener
{
    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $ormQuery = $config->getOrmQuery();
        $ormQuery
            ->addSelect('i.id as image')
            ->addSelect('(CASE WHEN p.image IS NOT NULL THEN true ELSE false END) as hasImage')
            ->addSelect('s.label as status')
            ->addLeftJoin('p.image', 'i')
            ->addLeftJoin('p.status', 's')
            ->addGroupBy('s.label');
        $columns = $config->offsetGetByPath('[columns]');
        $columns = array_merge(
            [
                'image' => [
                    'label' => 'marello.product.image.label',
                    'type' => 'twig',
                    'frontend_type' => 'html',
                    'template' => 'MarelloProductBundle:Product/Datagrid/Property:image.html.twig',
                ],
                'status' => [
                    'label' => 'marello.product.status.label',
                    'frontend_type' => 'string',
                ]
            ],
            $columns
        );
        $config->offsetSetByPath('[columns]', $columns);
        $config
            ->offsetSetByPath(
                '[sorters][columns][status]',
                ['data_name' => 's.label']
            )
            ->offsetSetByPath(
                '[filters][columns][image]',
                [
                    'type' => 'boolean',
                    'data_name' => 'hasImage',
                ]
            )
            ->offsetSetByPath(
                '[filters][columns][status]',
                [
                    'type' => 'entity',
                    'data_name' => 's',
                    'options' => [
                        'field_options' => [
                            'multiple' => true,
                            'class' => ProductStatus::class
                        ]
                    ]
                ]
            );
    }

    /**
     * @param OrmResultBefore $event
     */
    public function onResultBefore(OrmResultBefore $event)
    {
        $event->getQuery()->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, ProductsGridSqlWalker::class);
    }
    
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
                $part = str_replace('hasImage', '', $part);
                $part = str_replace(',,', ',', $part);
                $parts[$k] = $part;
            }
            $newGroupByParts[$key] = new GroupBy($parts);
        }
        $dataSource->getQueryBuilder()->resetDQLPart('groupBy');
        $dataSource->getQueryBuilder()->add('groupBy', $newGroupByParts);
    }
}
