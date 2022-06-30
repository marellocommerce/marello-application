<?php

namespace Marello\Bundle\HealthCheckBundle\EventListener\Datagrid;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\IntegrationBundle\Entity\Status;

class LastIntegrationStatusesGridListener
{
    private const PARAM_KEY = 'widgetConfiguration';

    /**
     * @var BigNumberDateHelper
     */
    private $dateHelper;

    public function __construct(BigNumberDateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
    }

    public function onPreBuild(PreBuild $event)
    {
        $parameters = $event->getParameters();
        if (!$parameters->has(self::PARAM_KEY)) {
            return;
        }

        $config = $event->getConfig();
        $config->offsetSetByPath(
            self::PARAM_KEY,
            $parameters->get(self::PARAM_KEY)
        );
    }

    public function buildAfter(BuildAfter $event)
    {
        $grid = $event->getDatagrid();
        $widgetConfiguration = $grid->getConfig()->offsetGetOr(self::PARAM_KEY);
        if (!$widgetConfiguration) {
            return;
        }

        $grid->getConfig()->offsetUnset(self::PARAM_KEY);
        /** @var OrmDatasource $dataSource */
        $dataSource = $grid->getDatasource();
        $queryBuilder = $dataSource->getQueryBuilder();
        $alias = $queryBuilder->getRootAliases()[0];

        if (isset($widgetConfiguration['code'])) {
            $codeValue = $widgetConfiguration['code'];
            if ($codeValue !== null) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . '.code', ':widgetConfigurationCode'));
                $queryBuilder->setParameter('widgetConfigurationCode', $codeValue);
            }
        }

        if (isset($widgetConfiguration['dateRange'])) {
            $dateRangeValue = $widgetConfiguration['dateRange'];
            [$start, $end] = $this->dateHelper->getPeriod($dateRangeValue, Status::class, 'date');
            if ($start) {
                $queryBuilder->andWhere($queryBuilder->expr()->gte($alias . '.date', ':widgetConfigurationStart'));
                $queryBuilder->setParameter('widgetConfigurationStart', $start);
            }
            if ($end) {
                $queryBuilder->andWhere($queryBuilder->expr()->lte($alias . '.date', ':widgetConfigurationEnd'));
                $queryBuilder->setParameter('widgetConfigurationEnd', $end);
            }
        }
    }
}
