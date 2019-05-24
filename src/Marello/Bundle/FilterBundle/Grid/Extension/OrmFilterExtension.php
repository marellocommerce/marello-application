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