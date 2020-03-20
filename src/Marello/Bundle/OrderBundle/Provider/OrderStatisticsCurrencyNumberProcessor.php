<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;

class OrderStatisticsCurrencyNumberProcessor
{
    /** @var OrderStatisticsCurrencyNumberFormatter */
    protected $numberFormatter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var OrderDashboardStatisticProvider */
    protected $valueProvider;

    /**
     * @param OrderStatisticsCurrencyNumberFormatter $numberFormatter
     * @param BigNumberDateHelper $dateHelper
     * @param OrderDashboardStatisticProvider $valueProvider
     */
    public function __construct(
        OrderStatisticsCurrencyNumberFormatter $numberFormatter,
        BigNumberDateHelper $dateHelper,
        OrderDashboardStatisticProvider $valueProvider
    ) {
        $this->numberFormatter = $numberFormatter;
        $this->dateHelper      = $dateHelper;
        $this->valueProvider   = $valueProvider;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param                 $getterName
     * @param bool   $lessIsBetter
     * @param bool   $lastWeek
     * @param string $comparable
     * @return array
     */
    public function getBigNumberValues(
        WidgetOptionBag $widgetOptions,
        $getterName,
        $lessIsBetter = false,
        $lastWeek = false,
        $comparable = 'true'
    ) {
        $lessIsBetter     = (bool)$lessIsBetter;
        $dateRange        = $lastWeek ? $this->dateHelper->getLastWeekPeriod() : $widgetOptions->get('dateRange');
        $value            = call_user_func([$this->valueProvider, $getterName], $dateRange, $widgetOptions);
        $salesChannel = $widgetOptions->get('salesChannel');
        $currencyCode = $salesChannel ? $salesChannel->getCurrency() : null;
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);
        $previousData     = [];
        $comparable       = $comparable == 'true' ? true : false;

        if (count($previousInterval)) {
            if ($comparable) {
                if ($lastWeek) {
                    $previousInterval = $this->dateHelper->getLastWeekPeriod(-1);
                }

                $previousData['value'] = call_user_func(
                    [$this->valueProvider, $getterName],
                    $previousInterval,
                    $widgetOptions
                );
                $previousData['dateRange']    = $previousInterval;
                $previousData['lessIsBetter'] = $lessIsBetter;
            }

            $previousData['comparable'] = $comparable;
        }

        return $this->numberFormatter->formatResult($value, $currencyCode, $previousData);
    }
}
