<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WarehouseController extends AbstractController
{
    /**
     * @Route(
     *     path="/update-default",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_warehouse_updatedefault"
     * )
     * @Template("MarelloInventoryBundle:Warehouse:updateDefault.html.twig")
     * @AclAncestor("marello_inventory_warehouse_update")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateDefaultAction(Request $request)
    {
        $aclHelper = $this->container->get('oro_security.acl_helper');
        $entity = $this->container->get('doctrine')
            ->getRepository(Warehouse::class)
            ->getDefault($aclHelper);

        $form = $this->createForm(WarehouseType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.inventory.messages.success.warehouse.saved')
            );
            return $this->redirectToRoute('marello_inventory_warehouse_updatedefault');
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }
}
