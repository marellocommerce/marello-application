<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Marello\Bundle\DataGridBundle\Helper\DatagridHelper;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class ProductSalesChannelColumnListener
{
    /** @var DatagridHelper $datagridHelper */
    protected $datagridHelper;

    /**
     * ProductSalesChannelColumnListener constructor.
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
        $this->datagridHelper->removeWhereClause();
        $this->datagridHelper->moveColumnToFront('hasChannel');
    }
}
