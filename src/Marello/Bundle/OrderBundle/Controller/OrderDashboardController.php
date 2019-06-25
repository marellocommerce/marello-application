<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrderDashboardController extends Controller
{
    /**
     * @Route(
     *      "/orderitems_by_status/chart/{widget}",
     *      name="marello_order_dashboard_orderitems_by_status_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("MarelloOrderBundle:Dashboard:orderitemsByStatus.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function orderitemsByStatusAction(Request $request, $widget)
    {
        $options = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($request->query->get('_widgetId', null));
        $valueOptions = [
            'field_name' => 'quantity'
        ];
        $items = $this->get('marello_order.provider.orderitems_by_status')
            ->getOrderItemsGroupedByStatus($options);
        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
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
}
