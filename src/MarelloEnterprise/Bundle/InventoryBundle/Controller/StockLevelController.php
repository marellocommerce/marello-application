<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Controller\StockLevelController as BaseController;
/**
 * @Config\Route("/stock-level")
 */
class StockLevelController extends BaseController
{
    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"}, name="marello_inventory_stocklevel_index")
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function indexAction(Product $product)
    {
        return [
            'product' => $product,
        ];
    }

    /**
     * @Config\Route("/chart/{id}", requirements={"id"="\d+"}, name="marello_inventory_stocklevel_chart")
     * @Config\Template
     *
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    public function chartAction(Product $product, Request $request)
    {
        /*
         * Create parameters required for chart.
         */
        $from     = new \DateTime($request->query->get('from', 'tomorrow - 1 second - 1 week'));
        $to       = new \DateTime($request->query->get('to', 'tomorrow - 1 second'));

        $items = $this
            ->get('marello_inventory.logging.chart_builder')
            ->getChartData($product, $from, $to);

        $viewBuilder = $this->container->get('oro_chart.view_builder');

        $chartView = $viewBuilder
            ->setArrayData($items)
            ->setOptions([
                'name'        => 'marelloinventory',
                'data_schema' => [
                    'label' => [
                        'field_name' => 'time',
                        'label'      => 'Time',
                    ],
                    'value' => [
                        'field_name' => 'inventory',
                        'label'      => 'Inventory Level',
                    ],
                ],
            ])
            ->getView();

        return compact('chartView', 'product');
    }
}
