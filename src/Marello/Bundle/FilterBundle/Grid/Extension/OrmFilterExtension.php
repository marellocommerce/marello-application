<?php

namespace Marello\Bundle\FilterBundle\Grid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Extension\Appearance\AppearanceExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\AbstractSorterExtension;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension as BaseOrmFilterExtension;

class OrmFilterExtension extends BaseOrmFilterExtension
{
    /**
     * {@inheritDoc}
     */
    protected function getValuesToApply(DatagridConfiguration $config, $readParameters = true)
    {
        $defaultFilters = $config->offsetGetByPath(Configuration::DEFAULT_FILTERS_PATH, []);

        if (!$readParameters) {
            return $defaultFilters;
        } else {
            $parameters = $this->getParameters();
            $currentFilters = $parameters->get(self::FILTER_ROOT_PARAM, []);
            if ($currentFilters === null) {
                $currentFilters = [];
            }
            $pager = $parameters->get(PagerInterface::PAGER_ROOT_PARAM, []);
            $appearance = $parameters->get(AppearanceExtension::APPEARANCE_ROOT_PARAM, []);
            $sorter = $parameters->get(AbstractSorterExtension::SORTERS_ROOT_PARAM, []);
            if (!empty($pager) || !empty($appearance) || !empty($sorter)) {
                return $currentFilters;
            } else {
                return array_replace($defaultFilters, $currentFilters);
            }
        }
    }
}
