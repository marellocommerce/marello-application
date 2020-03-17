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
                    'inline_editing' => ['enable' => false]
                ],
                'status' => [
                    'label' => 'marello.product.status.label',
                    'frontend_type' => 'string',
                    'inline_editing' => ['enable' => false]
                ]
            ],
            $columns
        );
        $config->offsetSetByPath('[columns]', $columns);

        $filters = $config->offsetGetByPath('[filters][columns]');
        $filters = array_merge(
            [
                'image' => [
                    'type' => 'boolean',
                    'data_name' => 'hasImage',
                ],
                'status' => [
                    'type' => 'entity',
                    'data_name' => 's',
                    'options' => [
                        'field_options' => [
                            'multiple' => true,
                            'class' => ProductStatus::class
                        ]
                    ]
                ]
            ],
            $filters
        );
        $config->offsetSetByPath('[filters][columns]', $filters);
        $config
            ->offsetSetByPath(
                '[sorters][columns][status]',
                ['data_name' => 's.label']
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
