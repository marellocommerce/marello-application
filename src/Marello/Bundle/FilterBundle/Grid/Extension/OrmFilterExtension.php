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

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $filtersState        = $data->offsetGetByPath('[state][filters]', []);
        $initialFiltersState = $data->offsetGetByPath('[initialState][filters]', []);
        $filtersMetaData     = [];

        $filters       = $this->getFiltersToApply($config);
        $values        = $this->getValuesToApply($config);
        $initialValues = $this->getValuesToApply($config, false);
        $lazy          = $data->offsetGetOr(MetadataObject::LAZY_KEY, true);
        $filtersParams = $this->getParameters()->get(self::FILTER_ROOT_PARAM, []);
        $rawConfig     = $this->configurationProvider->isApplicable($config->getName())
            ? $this->configurationProvider->getRawConfiguration($config->getName())
            : [];

        foreach ($filters as $filter) {
            if (!$lazy) {
                $filter->resolveOptions();
            }
            $name             = $filter->getName();
            $value            = $this->getFilterValue($values, $name);
            $initialValue     = $this->getFilterValue($initialValues, $name);
            $filtersState        = $this->updateFilterStateEnabled($name, $filtersParams, $filtersState);
            $filtersState        = $this->updateFiltersState($filter, $value, $filtersState);
            $initialFiltersState = $this->updateFiltersState($filter, $initialValue, $initialFiltersState);

            $filter->setFilterState($value);
            $metadata          = $filter->getMetadata();
            $filtersMetaData[] = array_merge(
                $metadata,
                [
                    'label' => $metadata[FilterUtility::TRANSLATABLE_KEY]
                        ? $this->translator->trans($metadata['label'])
                        : $metadata['label'],
                    'cacheId' => $this->getFilterCacheId($rawConfig, $metadata),
                    'value' => ['value' => (string)$value['value']]
                ]
            );
        }

        $data
            ->offsetAddToArray('initialState', ['filters' => $initialFiltersState])
            ->offsetAddToArray('state', ['filters' => $filtersState])
            ->offsetAddToArray('filters', $filtersMetaData)
            ->offsetAddToArray(MetadataObject::REQUIRED_MODULES_KEY, ['orofilter/js/datafilter-builder']);
    }
}