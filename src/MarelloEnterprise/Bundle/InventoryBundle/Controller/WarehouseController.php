<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Controller\WarehouseController as BaseController;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WarehouseController extends BaseController
{
    /**
     * @Route(
     *     path="/",
     *     methods={"GET"},
     *     name="marelloenterprise_inventory_warehouse_index"
     * )
     * @Template
     * @AclAncestor("marelloenterprise_inventory_warehouse_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Warehouse::class,
        ];
    }

    /**
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marelloenterprise_inventory_warehouse_create"
     * )
     * @Template("@MarelloEnterpriseInventory/Warehouse/update.html.twig")
     * @AclAncestor("marelloenterprise_inventory_warehouse_create")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createAction(Request $request)
    {
        return $this->update(new Warehouse(), $request);
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     name="marelloenterprise_inventory_warehouse_update"
     * )
     * @Template
     * @AclAncestor("marelloenterprise_inventory_warehouse_update")
     *
     * @param Warehouse $warehouse
     * @param Request   $request
     *
     * @return RedirectResponse
     */
    public function updateAction(Warehouse $warehouse, Request $request)
    {
        return $this->update($warehouse, $request);
    }


    /**
     * @Route(
     *     path="/view/{id}",
     *     methods={"GET"},
     *     requirements={"id"="\d+"},
     *     name="marelloenterprise_inventory_warehouse_view"
     * )
     * @Template
     * @AclAncestor("marelloenterprise_inventory_warehouse_view")
     *
     * @param Warehouse $warehouse
     *
     * @return array
     */
    public function viewAction(Warehouse $warehouse)
    {
        return [
            'entity' => $warehouse,
        ];
    }

    /**
     * @param Warehouse $warehouse
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    protected function update(Warehouse $warehouse, Request $request)
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $warehouse,
            $this->createForm(WarehouseType::class, $warehouse),
            $this->container
                ->get(TranslatorInterface::class)
                ->trans('marelloenterprise.inventory.messages.success.warehouse.saved'),
            $request,
            'marelloenterprise_inventory.form_handler.warehouse'
        );
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
            ]
        );
    }
}
