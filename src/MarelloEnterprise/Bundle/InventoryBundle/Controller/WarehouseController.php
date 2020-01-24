<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Controller\WarehouseController as BaseController;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WarehouseController extends BaseController
{
    /**
     * @Config\Route("/", name="marelloenterprise_inventory_warehouse_index")
     * @Config\Method("GET")
     * @Config\Template
     * @AclAncestor("marelloenterprise_inventory_warehouse_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Warehouse::class,
        ];
    }

    /**
     * @Config\Route("/create", name="marelloenterprise_inventory_warehouse_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("@MarelloEnterpriseInventory/Warehouse/update.html.twig")
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
     * @Config\Route("/update/{id}", name="marelloenterprise_inventory_warehouse_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
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
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marelloenterprise_inventory_warehouse_view")
     * @Config\Method({"GET"})
     * @Config\Template
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
        return $this->get('oro_form.update_handler')->update(
            $warehouse,
            $this->createForm(WarehouseType::class, $warehouse),
            $this->get('translator')->trans('marelloenterprise.inventory.messages.success.warehouse.saved'),
            $request,
            'marelloenterprise_inventory.form_handler.warehouse'
        );
    }
}
