<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InventoryLevelController extends Controller
{
    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventorylevel_index")
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function indexAction(InventoryItem $inventoryItem)
    {
        return [
            'product' => $inventoryItem->getProduct(),
            'inventoryItem' => $inventoryItem
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
