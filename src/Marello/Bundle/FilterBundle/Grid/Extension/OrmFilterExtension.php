<?php

namespace Marello\Bundle\FilterBundle\Grid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension as BaseOrmFilterExtension;
use Symfony\Component\HttpFoundation\RequestStack;

class OrmFilterExtension extends BaseOrmFilterExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * getValuesToApply removed and replaced by processConfigs
     * because of the removal of the function in the AbstractFilterExtension of Oro
     * https://github.com/oroinc/platform/commit/1b963e62da45f8490923943905db8e8c2f1308a9#diff-1383de7d0ef0720b4ae0e52643eda20d
     */

    /**
     * {@inheritDoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        parent::processConfigs($config);
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest && $currentRequest->isXmlHttpRequest()) {
            $filters = $config->offsetGetByPath(Configuration::FILTERS_PATH);
            unset($filters['default']);
            $config->offsetSetByPath(Configuration::FILTERS_PATH, $filters);
        }
    }
}
