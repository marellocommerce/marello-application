<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Config\Route("/warehouse")
 */
class WarehouseController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Method("GET")
     * @Config\Template
     * @AclAncestor("marello_inventory_warehouse_view")
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
     * @AclAncestor("marello_inventory_warehouse_create")
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
     * @Config\Route("/delete/{id}")
     * @Config\Method("DELETE")
     * @AclAncestor("marello_inventory_warehouse_delete")
     *
     * @param Warehouse $warehouse
     *
     * @return Response
     */
    public function deleteAction(Warehouse $warehouse)
    {
        $em = $this->getDoctrine()->getManager();

        if ($warehouse->isDefault()) {
            // TODO: Cannot remove default warehouse.
            $this->addFlash('error', 'Cannot delete default warehouse.');

            return new Response('Cannot delete default warehouse.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $em->remove($warehouse);
        $em->flush();

        return new Response();
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
}
