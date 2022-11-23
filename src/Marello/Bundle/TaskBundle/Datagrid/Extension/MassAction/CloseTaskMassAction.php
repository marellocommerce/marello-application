<?php

namespace Marello\Bundle\TaskBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;

class CloseTaskMassAction extends AjaxMassAction
{
    public function setOptions(ActionConfiguration $options)
    {
        if (!isset($options['frontend_type'])) {
            $options['frontend_type'] = '';
        }

        if (!isset($options['handler'])) {
            $options['handler'] = CloseTaskMassActionHandler::class;
        }

        $options['confirmation'] = false;

        return parent::setOptions($options);
    }
}
