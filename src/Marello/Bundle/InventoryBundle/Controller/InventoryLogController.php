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
            ->setArrayData($items)
            ->setOptions([
                'name'        => 'marelloinventory',
                'data_schema' => [
                    'label' => [
                        'type'       => 'datetime',
                        'field_name' => 'time',
                        'label'      => 'Time',
                    ],
                    'value' => [
                        'type'       => 'integer',
                        'field_name' => 'quantity',
                        'label'      => 'Quantity',
                    ],
                ]
            ])
            ->getView();

        return [
            'chartView' => $view,
        ];
    }
}
