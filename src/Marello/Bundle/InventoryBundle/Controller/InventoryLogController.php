<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Config\Route("/log")
 */
class InventoryLogController extends Controller
{
    /**
     * @Config\Route("/list/{id}", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function listAction(Product $product)
    {
        return [
            'product' => $product,
        ];
    }

    /**
     * @Config\Route("/chart/{id}", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    public function chartAction(Product $product, Request $request)
    {
        $from     = new \DateTime($request->query->get('from', 'now - 1 week'));
        $to       = new \DateTime($request->query->get('to', 'now'));
        $interval = $request->get('interval', '1 day');

        $items = $this
            ->get('marello_inventory.logging.chart_builder')
            ->getChartData($product, $from, $to, $interval);

        $viewBuilder = $this->container->get('oro_chart.view_builder');

        $view = $viewBuilder
            ->setOptions([
                'name'        => 'line_chart',
                'data_schema' => [
                    'label' => [
                        'type'       => 'string',
                        'field_name' => 'time',
                        'label'      => 'Time',
                    ],
                    'value' => [
                        'type'       => 'number',
                        'field_name' => 'quantity',
                        'label'      => 'Quantity',
                    ],
                ],
            ])
            ->setArrayData($items)
            ->getView();

        return [
            'chartView' => $view,
        ];
    }
}
