<?php

namespace Marello\Bundle\DataGridBundle\EventListener\Datagrid;

use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class RowSelectionSelectAllListener
{
    const ROW_SELECTION_OPTION_PATH              = '[options][rowSelection]';
    const REQUIREJS_MODULES_MODULES_OPTION_PATH  = '[options][requireJSModules]';
    const ROW_SELECTION_JS_MODULE_TO_BE_REPLACED = 'orodatagrid/js/datagrid/listener/column-form-listener';
    const ROW_SELECTION_JS_MODULE                = 'marellodatagrid/js/datagrid/listener/column-form-listener';
    const ROW_SELECT_ALL_JS_MODULE               = 'marellodatagrid/js/datagrid/listener/select-all-listener';
    const BOOLEAN_SELECT_ALL_COLUMN_TYPE         = 'boolean-select-row';

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();

        $config = $datagrid->getConfig();

        $rowSelectionConfig = $config->offsetGetByPath(self::ROW_SELECTION_OPTION_PATH, []);
        $columns = $config->offsetGetByPath('columns', []);
        if (!$this->isApplicable($rowSelectionConfig)) {
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

        if (in_array(self::ROW_SELECTION_JS_MODULE_TO_BE_REPLACED, $requireJsModules)) {
            $key = array_search(self::ROW_SELECTION_JS_MODULE_TO_BE_REPLACED, $requireJsModules);
            $requireJsModules[$key] = self::ROW_SELECTION_JS_MODULE;
        }

        if (!in_array(self::ROW_SELECT_ALL_JS_MODULE, $requireJsModules)) {
            $requireJsModules[] = self::ROW_SELECT_ALL_JS_MODULE;
        }

        $config->offsetSetByPath(self::REQUIREJS_MODULES_MODULES_OPTION_PATH, $requireJsModules);
    }

    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        $datagrid = $event->getDatagrid();

        $config = $datagrid->getConfig();

        $rowSelectionConfig = $config->offsetGetByPath(self::ROW_SELECTION_OPTION_PATH, []);
        if (!$this->isApplicable($rowSelectionConfig)) {
            return;
        }

        $properties = $config->offsetGetByPath('properties', []);
        $rowSelectionColumnName = $rowSelectionConfig['columnName'];
        /** @var Query $query */
        $query = $event->getQuery();
        $query
            ->setFirstResult(0)
            ->setMaxResults(null);

        $allResults = $query->getResult();
        $selectedAll = null;
        $selectedCnt = 0;
        $selectedRows = [];
        $allRowsIds = [];
        foreach ($allResults as $result) {
            $result['id'] = (string)$result['id'];
            $allRowsIds[] = $result['id'];
            if (isset($result[$rowSelectionColumnName]) && $result[$rowSelectionColumnName] === '1') {
                ++$selectedCnt;
                $selectedRows[] = $result;
            }
        }

        if ($selectedCnt === 0) {
            $selectedAll = false;
        } elseif (count($allResults) === $selectedCnt) {
            $selectedAll = true;
        }
        $properties['allSelected'] = [
            'type' => 'callback',
            'callable' => function () use ($selectedAll) {
                return $selectedAll;
            },
            PropertyInterface::FRONTEND_TYPE_KEY => 'string'
        ];
        $properties['selectedRows'] = [
            'type' => 'callback',
            'callable' => function () use ($selectedRows) {
                return $selectedRows;
            },
            PropertyInterface::FRONTEND_TYPE_KEY => 'row_array'
        ];
        $properties['allRowsIds'] = [
            'type' => 'callback',
            'callable' => function () use ($allRowsIds) {
                return $allRowsIds;
            },
            PropertyInterface::FRONTEND_TYPE_KEY => 'row_array'
        ];
        $config->offsetSetByPath('properties', $properties);
    }

    /**
     * @param $rowSelectionConfig
     * @return bool
     */
    protected function isApplicable($rowSelectionConfig)
    {
        return (is_array($rowSelectionConfig) && !empty($rowSelectionConfig['columnName']) &&
            !empty($rowSelectionConfig['selectAll']) && (bool)$rowSelectionConfig['selectAll']);
    }
}
