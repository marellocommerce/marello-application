<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Controller\InventoryController as BaseController;

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
     * @param Product $product
     *
     * @return array
     */
    public function viewAction(Product $product)
    {
        return [
            'entity' => $product,
        ];
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventory_update")
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Product $product)
    {
        $handler = $this->get('marello_inventory.form.handler.product_inventory');

        if ($handler->process($product)) {
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_inventory_inventory_update',
                    'parameters' => ['id' => $product->getId()],
                ],
                [
                    'route'      => 'marello_product_view',
                    'parameters' => ['id' => $product->getId()],
                ],
                $product
            );
        }

        return [
            'form'   => $handler->getFormView(),
            'entity' => $product,
        ];
    }

    /**
     * @Config\Route("/widget/info/{id}", name="marello_inventory_widget_info", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Product $product
     *
     * @return array
     */
    public function infoAction(Product $product)
    {
        $item = $product->getInventoryItems()->first();

        return [
            'item' => $item,
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
