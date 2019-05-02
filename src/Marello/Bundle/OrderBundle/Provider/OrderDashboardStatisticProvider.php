<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository;
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
     * @return int
     */
    public function getTotalRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');
        return $this->orderRepository->getTotalRevenueValue($start, $end);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getTotalOrdersNumberValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->orderRepository->getTotalOrdersNumberValue($start, $end);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getAverageOrderValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->orderRepository->getAverageOrderValue($start, $end);
    }
}
