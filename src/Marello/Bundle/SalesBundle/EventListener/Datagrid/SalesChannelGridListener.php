<?php

namespace Marello\Bundle\SalesBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class SalesChannelGridListener
{
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $config->offsetSetByPath(
            '[properties][config_link]',
            [
                'type'   => 'url',
                'route'  => 'marello_sales_config_saleschannel',
                'params' => ['id'],
            ]
        );
        $config->offsetSetByPath(
            '[actions][config]',
            [
                'type'         => 'navigate',
                'label'        => 'marello.sales.sales_channel.grid.action.config.label',
                'link'         => 'config_link',
                'icon'         => 'cog',
                'acl_resource' => 'marello_sales_saleschannel_update',
                'order'        => 999,
            ]
        );
    }
}
