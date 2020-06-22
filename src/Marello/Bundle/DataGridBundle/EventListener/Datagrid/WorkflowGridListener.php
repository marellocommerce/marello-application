<?php

namespace Marello\Bundle\DataGridBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class WorkflowGridListener
{
    /**
     * @param BuildAfter $event
     */
    public function removeWorkflow(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $config = $datagrid->getConfig();
        $this->removeWorkflowStepColumn($config, ['workflowStepLabel']);
    }

    /**
     * @param DatagridConfiguration $config
     * @param array $workflowStepColumns
     */
    protected function removeWorkflowStepColumn(DatagridConfiguration $config, array $workflowStepColumns)
    {
        $paths = [
            '[columns]',
            '[filters][columns]',
            '[sorters][columns]'
        ];

        foreach ($paths as $path) {
            $columns = $config->offsetGetByPath($path, []);
            foreach ($workflowStepColumns as $column) {
                if (!empty($columns[$column])) {
                    unset($columns[$column]);
                }
            }
            $config->offsetSetByPath($path, $columns);
        }
    }
}
