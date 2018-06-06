<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

use Marello\Bundle\OrderBundle\Entity\Repository\OrderItemRepository;
use Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;

class OrderItemDashboardStatisticProvider
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @param OrderItemRepository $orderItemRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderItemRepository $orderItemRepository, OrderRepository $orderRepository) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return int
     */
    public function getTopProductsByRevenue(WidgetOptionBag $widgetOptions)
    {
        $quantity = $widgetOptions->get('quantity') ? : 3;
        $currencies = $this->orderRepository->getOrdersCurrencies();

        $result = [];
        foreach ($currencies as $currency) {
            $currency = $currency['currency'];
            $items = $this->orderItemRepository->getTopProductsByRevenue($quantity, $currency);
            if (!empty($items)) {
                $result[$currency] = $items;
            }
        }

        return $result;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return int
     */
    public function getTopProductsByItemsSold(WidgetOptionBag $widgetOptions)
    {
        $quantity = $widgetOptions->get('quantity') ? : 3;

        return $this->orderItemRepository->getTopProductsByItemsSold($quantity);
    }
}
