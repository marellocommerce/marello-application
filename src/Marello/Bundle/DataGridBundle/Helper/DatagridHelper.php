<?php

namespace Marello\Bundle\DataGridBundle\Helper;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class DatagridHelper
{
    const DATAGRID_COLUMNS_NAME  = '[columns][%s]';
    
    const DATAGRID_FILTERS_NAME  = '[filters][columns][%s]';
        
    const DATAGRID_SORTERS_NAME  = '[sorters][columns][%s]';

    /** @var DatagridConfiguration $gridConfig */
    protected $gridConfig;

    /**
     * Move a column to front of the column config based on new config
     * option 'add_before'. Will only be applicable if the grid is
     * extended from other grid
     * @param $columnName
     */
    public function moveColumnToFront($columnName)
    {
        if (!$this->isApplicable($columnName)) {
            return;
        }

        $column = $this->gridConfig->offsetGetByPath(sprintf(self::DATAGRID_COLUMNS_NAME, $columnName));
        if (isset($column['add_before']) && !empty($column['add_before']) && true === $column['add_before']) {
            $columnsConfig = $this->gridConfig->toArray(['columns']);
            // remove the column first
            unset($columnsConfig['columns'][$columnName]);

            // create correct path for column
            $column = [$columnName => $column];

            // merge the two arrays with the column to move as first array
            $columnsConfig['columns'] = array_merge($column, $columnsConfig['columns']);

            // set new grid config
            $this->gridConfig->offsetSetByPath('[columns]', $columnsConfig['columns']);
        }
    }

    /**
     * remove where clause from config if option removeWhereClause is configured
     */
    public function removeWhereClause()
    {
        if (is_null($this->gridConfig->offsetGetByPath('[options][removeWhereClause]'))) {
            return;
        }

        if (true === $this->gridConfig->offsetGetByPath('[options][removeWhereClause]')) {
            $this->gridConfig->offsetUnsetByPath('[source][query][where]');
        }
    }

    /**
     * Set grid config for current event
     * @param DatagridConfiguration $config
     */
    public function setGridConfig(DatagridConfiguration $config)
    {
        $this->gridConfig = $config;
    }

    /**
     * Check if we can perform the actions on this grid
     * @param $columnName
     * @return bool
     */
    private function isApplicable($columnName)
    {
        if (!$columnName) {
            throw new \LogicException(sprintf('Cannot remove column fieldname is not specified.'));
        }

        if (!$this->gridConfig) {
            throw new \LogicException(sprintf('Cannot remove column, grid config is not specified.'));
        }

        if (is_null($this->gridConfig->offsetGetByPath('[extended_from]'))) {
            return false;
        }

        return true;
    }
}
