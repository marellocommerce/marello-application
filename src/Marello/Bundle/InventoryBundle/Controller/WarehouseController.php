<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WarehouseController extends Controller
{
    /**
     * @Config\Route("/update-default", requirements={"id"="\d+"}, name="marello_inventory_warehouse_updatedefault")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloInventoryBundle:Warehouse:updateDefault.html.twig")
     * @AclAncestor("marello_inventory_warehouse_update")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateDefaultAction(Request $request)
    {
        $entity = $this->getDoctrine()
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        $form = $this->createForm('marello_warehouse', $entity);
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
