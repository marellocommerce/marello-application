<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;

class OrderDashboardStatisticProvider
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var BigNumberDateHelper
     */
    protected $dateHelper;

    /**
     * @param OrderRepository $orderRepository
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        OrderRepository $orderRepository,
        BigNumberDateHelper $dateHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     * @return int
     */
    public function getTotalRevenueValues($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');
        return $this->orderRepository->getTotalRevenueValue($start, $end, $widgetOptions->get('salesChannel'));
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     * @return int
     */
    public function getTotalOrdersNumberValues($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');
        return $this->orderRepository->getTotalOrdersNumberValue($start, $end, $widgetOptions->get('salesChannel'));
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     * @return int
     */
    public function getAverageOrderValues($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->orderRepository->getAverageOrderValue($start, $end, $widgetOptions->get('salesChannel'));
    }
}
