<?php

namespace Marello\Bundle\WorkflowBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;

class WorkflowTransitMassAction extends AjaxMassAction
{
    protected const DEFAULT_BATCH_SIZE = 25;
    protected const DEFAULT_REPORT_TEMPLATE = 'workflow_transit_report';
    protected $requiredOptions = ['handler', 'entity_name', 'workflow', 'transition', 'batch_size', 'report_template'];

    public function setOptions(ActionConfiguration $options)
    {
        if (!isset($options['batch_size'])) {
            $options['batch_size'] = static::DEFAULT_BATCH_SIZE;
        }

        if (!isset($options['report_template'])) {
            $options['report_template'] = self::DEFAULT_REPORT_TEMPLATE;
        }

        if (!isset($options['frontend_type'])) {
            $options['frontend_type'] = '';
        }

        if (!isset($options['handler'])) {
            $options['handler'] = WorkflowTransitMassActionHandler::class;
        }

        $options['confirmation'] = false;

        return parent::setOptions($options);
    }
}
