<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelManageBatchesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InventoryLevelController extends AbstractController
{
    /**
     * @Route(
     *     path="/{id}", 
     *     requirements={"id"="\d+"}, 
     *     name="marello_inventory_inventorylevel_index"
     * )
     * @Template
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
     * @Route(
     *     path="/chart/{id}", 
     *     requirements={"id"="\d+"}, 
     *     name="marello_inventory_inventorylevel_chart"
     * )
     * @Template
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


    /**
     * @Route(
     *     path="/manage-batches/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_inventorylevel_manage_batches"
     * )
     * @Template
     *
     * @param InventoryLevel $inventoryLevel
     * @param Request $request
     *
     * @return array
     */
    public function manageBatchesAction(InventoryLevel $inventoryLevel, Request $request)
    {
        $result = $this->get('oro_form.update_handler')->update(
            $inventoryLevel,
            $this->createForm(InventoryLevelManageBatchesType::class, $inventoryLevel),
            $this->get('translator')->trans('marello.inventory.messages.success.inventorybatches.saved'),
            $request
        );

        return $result;
    }
}
