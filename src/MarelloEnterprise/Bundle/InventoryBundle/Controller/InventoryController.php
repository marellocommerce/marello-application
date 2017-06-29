<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Controller\InventoryController as BaseController;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

/**
 * @Config\Route("/item")
 */
class InventoryController extends BaseController
{
    /**
     * @Config\Route("/", name="marello_inventory_inventory_index")
     * @Config\Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => 'Marello\Bundle\InventoryBundle\Entity\InventoryItem',
        ];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventory_view")
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function viewAction(InventoryItem $inventoryItem)
    {
        return [
            'entity' => $inventoryItem,
        ];
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventory_update")
     * @Config\Template
     * @param InventoryItem $inventoryItem
     *
     * @return array|RedirectResponse
     */
    public function updateAction(InventoryItem $inventoryItem)
    {
        $handler = $this->get('marello_inventory.form.handler.inventory_item');

        if ($handler->process($inventoryItem)) {
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_inventory_inventory_update',
                    'parameters' => ['id' => $inventoryItem->getId()],
                ],
                [
                    'route'      => 'marello_inventory_inventory_view',
                    'parameters' => ['id' => $inventoryItem->getId()],
                ],
                $inventoryItem
            );
        }

        return [
            'form'   => $handler->getFormView(),
            'entity' => $inventoryItem,
            'product' => $inventoryItem->getProduct()
        ];
    }

    /**
     * @Config\Route("/widget/info/{id}", name="marello_inventory_widget_info", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function infoAction(InventoryItem $inventoryItem)
    {
        return [
            'item' => $inventoryItem,
        ];
    }

    /**
     * @Config\Route("/widget/datagrid/{id}", name="marello_inventory_widget_datagrid", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function datagridAction(Product $product)
    {
        $item = $product->getInventoryItems()->first();

        return [
            'item' => $item,
        ];
    }
}
