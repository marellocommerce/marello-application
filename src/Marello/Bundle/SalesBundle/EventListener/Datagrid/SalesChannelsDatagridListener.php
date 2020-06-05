<?php

namespace Marello\Bundle\SalesBundle\EventListener\Datagrid;

use Marello\Bundle\ProductBundle\EventListener\Datagrid\AbstractProductsGridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class SalesChannelsDatagridListener extends AbstractProductsGridListener
{
    const CHANNELS_COLUMN = 'salesChannels';

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
        $config->offsetSetByPath(sprintf('[columns][%s]', self::CHANNELS_COLUMN), [
            'label' => 'marello.product.channels.label',
            'type' => 'twig',
            'frontend_type' => 'html',
            'template' => 'MarelloSalesBundle:SalesChannel/Datagrid/Property:channels.html.twig',
            'renderable' => false
        ]);
    }
}
