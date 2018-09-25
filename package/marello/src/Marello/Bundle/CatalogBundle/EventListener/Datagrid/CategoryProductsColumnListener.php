<?php

namespace Marello\Bundle\CatalogBundle\EventListener\Datagrid;

use Marello\Bundle\DataGridBundle\Helper\DatagridHelper;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class CategoryProductsColumnListener
{
    /**
     * @var DatagridHelper $datagridHelper
     */
    protected $datagridHelper;

    /**
     * @param DatagridHelper $datagridHelper
     */
    public function __construct(DatagridHelper $datagridHelper)
    {
        $this->datagridHelper = $datagridHelper;
    }

    /**
     *
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $gridConfig = $event->getConfig();

        $this->datagridHelper->setGridConfig($gridConfig);
        $this->datagridHelper->moveColumnToFront('inCategory');
    }
}
