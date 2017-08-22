<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Controller\InventoryLevelController as BaseController;
/**
 * @Config\Route("/inventory-level")
 */
class InventoryLevelController extends BaseController
{
    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventorylevel_index")
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
     * @Config\Route("/chart/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventorylevel_chart")
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     * @param Request $request
     *
     * @return array
     */
    public function chartAction(InventoryItem $inventoryItem, Request $request)
    {
        /*
         * Create parameters required for chart.
         */
        $from     = new \DateTime($request->query->get('from', 'tomorrow - 1 second - 1 week'));
        $to       = new \DateTime($request->query->get('to', 'tomorrow - 1 second'));

        $items = $this
            ->get('marello_inventory.logging.chart_builder')
            ->getChartData($inventoryItem, $from, $to);

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

        return compact('chartView', 'inventoryItem');
    }
}
