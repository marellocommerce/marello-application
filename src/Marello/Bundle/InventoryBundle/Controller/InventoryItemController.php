<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Form\Type\InventoryItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InventoryItemController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_inventory_inventory_index"
     * )
     * @Template("MarelloInventoryBundle:Inventory:index.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => InventoryItem::class,
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_inventory_view"
     * )
     * @Template("MarelloInventoryBundle:Inventory:view.html.twig")
     * @Acl(
     *      id="marello_inventory_inventory_view",
     *      type="entity",
     *      class="MarelloInventoryBundle:InventoryItem",
     *      permission="VIEW"
     * )
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function viewAction(InventoryItem $inventoryItem)
    {
        return [
            'entity' => $inventoryItem,
            'product' => $inventoryItem->getProduct()
        ];
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_inventory_update"
     * )
     * @Template("MarelloInventoryBundle:Inventory:update.html.twig")
     * @Acl(
     *      id="marello_inventory_inventory_update",
     *      type="entity",
     *      class="MarelloInventoryBundle:InventoryItem",
     *      permission="EDIT"
     * )
     * @param InventoryItem $inventoryItem
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateAction(InventoryItem $inventoryItem, Request $request)
    {
        $result = $this->get('oro_form.update_handler')->update(
            $inventoryItem,
            $this->createForm(InventoryItemType::class, $inventoryItem),
            $this->get('translator')->trans('marello.inventory.messages.success.inventoryitem.saved'),
            $request
        );
        
        return $result;
    }


    /**
     * @Route(
     *     path="/widget/info/{id}",
     *     name="marello_inventory_widget_info",
     *     requirements={"id"="\d+"}
     * )
     * @Template("MarelloInventoryBundle:Inventory/widget:info.html.twig")
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function infoAction(InventoryItem $inventoryItem)
    {
        return [
            'item' => $inventoryItem,
            'product' => $inventoryItem->getProduct()
        ];
    }

    /**
     * @Route(
     *     path="/widget/datagrid/{id}",
     *     name="marello_inventory_widget_datagrid",
     *     requirements={"id"="\d+"}
     * )
     * @Template("MarelloInventoryBundle:Inventory/widget:datagrid.html.twig")
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function datagridAction(InventoryItem $inventoryItem)
    {
        return [
            'item' => $inventoryItem,
        ];
    }
}
