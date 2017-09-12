<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Controller\WarehouseController as BaseController;

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
            'entity_class' => 'Marello\Bundle\InventoryBundle\Entity\Warehouse',
        ];
    }

    /**
     * @Config\Route("/create")
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
        return $this->update($request);
    }

    /**
     * @Config\Route("/update/{id}")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_inventory_warehouse_update")
     *
     * @param Warehouse $warehouse
     * @param Request   $request
     *
     * @return RedirectResponse
     */
    public function updateAction(Warehouse $warehouse, Request $request)
    {
        return $this->update($request, $warehouse);
    }


    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"})
     * @Config\Method({"GET"})
     * @Config\Template
     * @AclAncestor("marello_inventory_warehouse_view")
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
     * @param Request        $request
     * @param Warehouse|null $warehouse
     *
     * @return RedirectResponse
     */
    protected function update(Request $request, Warehouse $warehouse = null)
    {
        if (!$warehouse) {
            $warehouse = new Warehouse();

            /** @var UsernamePasswordOrganizationToken $token */
            $token = $this->get('security.token_storage')->getToken();

            $warehouse->setOwner($token->getOrganizationContext());
        }

        $form = $this->createForm('marello_warehouse', $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($warehouse);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marelloenterprise.inventory.messages.success.warehouse.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marelloenterprise_inventory_warehouse_update',
                    'parameters' => ['id' => $warehouse->getId()],
                ],
                [
                    'route' => 'marelloenterprise_inventory_warehouse_index',
                ],
                $warehouse
            );
        }

        return [
            'entity' => $warehouse,
            'form'   => $form->createView(),
        ];
    }

    /**
     * @Config\Route("/address-book/{id}", requirements={"id"="\d+"})
     * @Config\Template("MarelloEnterpriseInventoryBundle:Warehouse/widget:addressBook.html.twig")
     * @AclAncestor("marello_inventory_warehouse_view")
     *
     * @param Warehouse $warehouse
     * @return array
     */
    public function addressBookAction(Warehouse $warehouse)
    {
        return array(
            'entity' => $warehouse,
            'address_edit_acl_resource' => 'marello_inventory_warehouse_update'
        );
    }
}
