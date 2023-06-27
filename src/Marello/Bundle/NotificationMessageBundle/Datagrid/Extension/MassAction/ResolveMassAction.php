<?php

namespace Marello\Bundle\NotificationMessageBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;

class ResolveMassAction extends AjaxMassAction
{
    protected $requiredOptions = ['handler'];

    public function setOptions(ActionConfiguration $options)
    {
        if (!isset($options['frontend_type'])) {
            $options['frontend_type'] = '';
        }

        if (!isset($options['handler'])) {
            $options['handler'] = ResolveMassActionHandler::class;
        }

        $options['confirmation'] = true;

        return parent::setOptions($options);
    }
}
