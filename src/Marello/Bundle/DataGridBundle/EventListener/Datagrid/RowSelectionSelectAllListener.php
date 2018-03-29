<?php

namespace Marello\Bundle\DataGridBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class RowSelectionSelectAllListener
{
    const ROW_SELECTION_OPTION_PATH             = '[options][rowSelection]';
    const REQUIREJS_MODULES_MODULES_OPTION_PATH = '[options][requireJSModules]';
    const ROW_SELECTION_JS_MODULE               = 'marellodatagrid/js/datagrid/listener/select-all-listener';
    const BOOLEAN_SELECT_ALL_COLUMN_TYPE        = 'boolean-select-row';

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();

        $config = $datagrid->getConfig();

        $rowSelectionConfig = $config->offsetGetByPath(self::ROW_SELECTION_OPTION_PATH, []);
        $columns = $config->offsetGetByPath('columns', []);

        if (!is_array($rowSelectionConfig) || empty($rowSelectionConfig['columnName']) ||
            empty($rowSelectionConfig['selectAll']) || $rowSelectionConfig['selectAll'] === false) {
            return;
        }
        foreach ($columns as $name => $attributes) {
            if ($name === $rowSelectionConfig['columnName']) {
                $columns[$name]['frontend_type'] = self::BOOLEAN_SELECT_ALL_COLUMN_TYPE;
                break;
            }
        }
        $config->offsetSetByPath('columns', $columns);

        // Add frontend module to handle selection
        $requireJsModules = $config->offsetGetByPath(self::REQUIREJS_MODULES_MODULES_OPTION_PATH, []);

        if (!$requireJsModules || !is_array($requireJsModules)) {
            $requireJsModules = [];
        }

        if (!in_array(self::ROW_SELECTION_JS_MODULE, $requireJsModules)) {
            $requireJsModules[] = self::ROW_SELECTION_JS_MODULE;
        }

        $config->offsetSetByPath(self::REQUIREJS_MODULES_MODULES_OPTION_PATH, $requireJsModules);
    }
}
