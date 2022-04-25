<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryLevelManageBatchesType;
use Marello\Bundle\InventoryBundle\Logging\ChartBuilder;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryLevelController extends AbstractController
{
    /**
     * @Route(
     *     path="/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_inventorylevel_index"
     * )
     * @Template("@MarelloInventory/InventoryLevel/index.html.twig")
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
     * @Template("@MarelloInventory/InventoryLevel/chart.html.twig")
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
            ->container
            ->get(ChartBuilder::class)
            ->getChartData($inventoryItem, $from, $to);

        $viewBuilder = $this->container->get(ChartViewBuilder::class);

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
        $inventoryItem = $inventoryLevel->getInventoryItem();
        if (!$inventoryItem->isEnableBatchInventory()) {
            $this->addFlash(
                'warning',
                'marello.inventory.messages.warning.inventorybatches.not_enabled'
            );

            return $this->redirect($this->generateUrl(
                'marello_inventory_inventory_update',
                ['id' => $inventoryItem->getId()]
            ));
        }

        return $this->container->get(UpdateHandlerFacade::class)->update(
            $inventoryLevel,
            $this->createForm(InventoryLevelManageBatchesType::class, $inventoryLevel),
            $this->container->get(TranslatorInterface::class)->trans('marello.inventory.messages.success.inventorybatches.saved'),
            $request
        );
    }

    /**
     * @Route(
     *     path="/manage-batches/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_inventorylevel_batches_view"
     * )
     * @Template("@MarelloInventory/InventoryLevel/batchesView.html.twig")
     *
     * @param InventoryLevel $inventoryLevel
     *
     * @return array
     */
    public function viewAction(InventoryLevel $inventoryLevel)
    {
        return [
            'entity' => $inventoryLevel
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ChartViewBuilder::class,
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
                ChartBuilder::class,
            ]
        );
    }
}
