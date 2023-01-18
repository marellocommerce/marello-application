<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

use Marello\Bundle\OrderBundle\Entity\Repository\OrderItemRepository;
use Marello\Bundle\OrderBundle\Entity\Repository\OrderRepository;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderItemDashboardStatisticProvider
{
    protected $medalImages = [
        'bundles/marelloorder/img/first.svg',
        'bundles/marelloorder/img/second.svg',
        'bundles/marelloorder/img/third.svg'
    ];

    public function __construct(
        protected OrderItemRepository $orderItemRepository,
        protected OrderRepository $orderRepository,
        protected ProductRepository $productRepository,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getTopProductsByRevenue(WidgetOptionBag $widgetOptions)
    {
        $quantity = $widgetOptions->get('quantity') ? : 3;
        $dateRange = $widgetOptions->get('dateRange');
        $currencies = $this->orderRepository->getOrdersCurrencies($this->aclHelper);

        $result = [];
        foreach ($currencies as $currency) {
            $currency = $currency['currency'];
            $items = $this->orderItemRepository->getTopProductsByRevenue($quantity, $currency, $dateRange);
            if (!empty($items)) {
                foreach ($items as $key => $item) {
                    $product = $this->productRepository->find($item['id']);
                    $items[$key]['medal'] = $this->medalImages[$key];
                    $items[$key]['product'] = $product;
                    $items[$key]['image'] = $product->getImage();
                }
                $result[$currency] = $items;
            }
        }

        return $result;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getTopProductsByItemsSold(WidgetOptionBag $widgetOptions)
    {
        $quantity = $widgetOptions->get('quantity') ? : 3;
        $dateRange = $widgetOptions->get('dateRange');
        $items = $this->orderItemRepository->getTopProductsByItemsSold($quantity, $dateRange);
        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $product = $this->productRepository->find($item['id']);
                $items[$key]['medal'] = $this->medalImages[$key];
                $items[$key]['product'] = $product;
                $items[$key]['image'] = $product->getImage();
            }
        }
        
        return $items;
    }
}
