<?php

namespace Marello\Bundle\CatalogBundle\EventListener\Datagrid;

use Marello\Bundle\ProductBundle\EventListener\Datagrid\AbstractProductsGridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class CategoriesDatagridListener extends AbstractProductsGridListener
{
    const CATEGORIES_COLUMN = 'cat';

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $this->addColumn($config);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addColumn(DatagridConfiguration $config)
    {
        $config->offsetSetByPath(sprintf('[columns][%s]', self::CATEGORIES_COLUMN), [
            'label' => 'marello.catalog.category.entity_plural_label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloCatalogBundle:Datagrid/Property:categories.html.twig',
            'renderable' => false
        ]);
    }
}
