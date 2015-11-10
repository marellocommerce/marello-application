<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
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
        $factory   = $this->get('marello_inventory.model.factory.product_inventory');
        $inventory = $factory->getProductInventory($product);

        return [
            'entity'    => $product,
            'inventory' => $inventory,
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
        /*
         * Create product inventory model for update page.
         */
        $factory   = $this->get('marello_inventory.model.factory.product_inventory');
        $inventory = $factory->getProductInventory($product);
        $form      = $this->createForm('marello_product_inventory', $inventory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /*
             * Retrieve inventory item entities from model.
             */
            $items = $inventory->getModifiedInventoryItems();

            /*
             * Persist inventory items which have quantity > 0.
             * Remove inventory items which are already in database but now have quantity = 0.
             */
            $items->map(function (InventoryItem $item) use ($em) {
                if ($item->getQuantity()) {
                    $em->persist($item);
                } elseif ($item->getId() && !$item->getQuantity()) {
                    $em->remove($item);
                }
            });

            $em->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_inventory_inventory_update',
                    'parameters' => ['id' => $product->getId()],
                ],
                ['route' => 'marello_inventory_inventory_index'],
                $product
            );
        }

        return [
            'form'   => $form->createView(),
            'entity' => $product,
        ];
    }
}
