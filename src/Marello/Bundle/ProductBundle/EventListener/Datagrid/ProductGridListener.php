<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Doctrine\ORM\Query;
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
            ->addLeftJoin('p.image', 'i');
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
}
