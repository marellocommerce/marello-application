<?php

namespace Marello\Bundle\HealthCheckBundle\Provider\Datagrid;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;

class LastIntegrationStatusesDataProvider
{
    public function getCodeValue(WidgetOptionBag $widgetOptions)
    {
        return $widgetOptions->get('code', []);
    }

    public function getDateRangeValue(WidgetOptionBag $widgetOptions)
    {
        return $widgetOptions->get('dateRange', []);
    }
}
