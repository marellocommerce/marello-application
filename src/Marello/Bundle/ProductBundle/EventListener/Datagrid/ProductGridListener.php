<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\GroupBy;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\ProductBundle\Datagrid\ORM\Query\ProductsGridSqlWalker;

class ProductGridListener
{
    protected $excludedGroupByFields  = ['hasImage', 'status'];

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
            ->addSelect('IDENTITY(p.status) as status')
            ->addLeftJoin('p.image', 'i')
            ->addGroupBy('i.id');
        $columns = $config->offsetGetByPath('[columns]');
        $columns = array_merge(
            [
                'image' => [
                    'label' => 'marello.product.image.label',
                    'type' => 'twig',
                    'frontend_type' => 'html',
                    'template' => '@MarelloProduct/Product/Datagrid/Property/image.html.twig',
                    'inline_editing' => ['enable' => false]
                ],
                'status' => [
                    'label' => 'marello.product.status.label',
                    'frontend_type' => 'select'
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
                    'data_name' => 'status',
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
                ['data_name' => 'status']
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
            $newGroupByParts[$key] = $this->formatGroupByParts($groupByPart->getParts());
        }
        $dataSource->getQueryBuilder()->resetDQLPart('groupBy');
        $dataSource->getQueryBuilder()->add('groupBy', $newGroupByParts);

        $config = $grid->getConfig();
        $columnsConfig = $this->switchColumns($config, 'createdAt', 'tags');
        $config->offsetSetByPath('[columns]', $columnsConfig);
    }

    /**
     * @param array $groupByParts
     * @return GroupBy
     */
    protected function formatGroupByParts(array $groupByParts)
    {
        $parts = [];
        foreach ($groupByParts as $k => $part) {
            $parts[$k] = $this->removeFieldsFromGroupBy($this->excludedGroupByFields, $part);
        }

        return new GroupBy($parts);
    }

    /**
     * @param array $fields
     * @param $part
     * @return mixed
     */
    protected function removeFieldsFromGroupBy(array $fields, $part)
    {
        foreach ($fields as $field) {
            $part = str_replace($field, '', $part);
            $part = str_replace(',,', ',', $part);
        }

        return $part;
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     * @param $firstColumnName
     * @param $secondColumnName
     * @return array
     */
    private function switchColumns(DatagridConfiguration $datagridConfig, $firstColumnName, $secondColumnName)
    {
        $columns = $datagridConfig->offsetGetByPath('[columns]');
        if (array_key_exists($firstColumnName, $columns)) {
            $firstColumnConfig = $columns[$firstColumnName];
            if (array_key_exists($secondColumnName, $columns)) {
                $secondColumnConfig = $columns[$secondColumnName];
                unset($columns[$firstColumnName]);
                unset($columns[$secondColumnName]);
                $columns = array_merge(
                    $columns,
                    [
                        $secondColumnName => $secondColumnConfig,
                        $firstColumnName => $firstColumnConfig
                    ]
                );

                return $columns;
            }
        }
        return $columns;
    }
}
