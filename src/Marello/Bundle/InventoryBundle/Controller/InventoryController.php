<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Config\Route("/item")
 */
class InventoryController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => 'Marello\Bundle\InventoryBundle\Entity\InventoryItem',
        ];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"})
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
}
