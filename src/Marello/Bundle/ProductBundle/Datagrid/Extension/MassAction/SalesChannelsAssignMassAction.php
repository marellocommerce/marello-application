<?php

namespace Marello\Bundle\ProductBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\AbstractMassAction;

class SalesChannelsAssignMassAction extends AbstractMassAction
{
    /**
     * @var string
     */
    private $route;

    /**
     * @param string $route
     */
    public function __construct($route)
    {
        $this->route = $route;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['frontend_type'])) {
            $options['frontend_type'] = '';
        }

        if (empty($options['route'])) {
            $options['route'] = $this->route;
        }

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = [];
        }

        if (empty($options['frontend_handle'])) {
            $options['frontend_handle'] = 'ajax';
        }

        return parent::setOptions($options);
    }
}
