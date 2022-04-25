<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Marello\Bundle\OrderBundle\Provider\OrderDashboardOrderItemsByStatusProvider;

class OrderDashboardController extends AbstractController
{
    /**
     * @Route(
     *      path="/orderitems_by_status/chart/{widget}",
     *      name="marello_order_dashboard_orderitems_by_status_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("@MarelloOrder/Dashboard/orderitemsByStatus.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function orderitemsByStatusAction(Request $request, $widget)
    {
        $options = $this->container->get(WidgetConfigs::class)
            ->getWidgetOptions($request->query->get('_widgetId', null));
        $valueOptions = [
            'field_name' => 'quantity'
        ];
        $items = $this->container->get(OrderDashboardOrderItemsByStatusProvider::class)
            ->getOrderItemsGroupedByStatus($options);
        $widgetAttr              = $this->container->get(WidgetConfigs::class)->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->container->get(ChartViewBuilder::class)
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'horizontal_bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => $valueOptions
                    ]
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            WidgetConfigs::class,
            ChartViewBuilder::class,
            OrderDashboardOrderItemsByStatusProvider::class
        ]);
    }
}
