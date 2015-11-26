<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Product $product, Request $request)
    {
        $form = $this->createForm('marello_product_inventory', $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($product);
            $em->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_inventory_inventory_update',
                    'parameters' => ['id' => $product->getId()],
                ],
                [   'route' => 'marello_product_view',
                    'parameters' => ['id' => $product->getId()]
                ],
                $product
            );
        }

        return [
            'form'   => $form->createView(),
            'entity' => $product,
        ];
    }

    /**
     * @Config\Route("/widget/info/{id}", name="marello_inventory_widget_info", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Product $product
     * @return array
     */
    public function infoAction(Product $product)
    {
        $item = $product->getInventoryItems()->first();
        return [
            'item' => $item
        ];
    }
}
