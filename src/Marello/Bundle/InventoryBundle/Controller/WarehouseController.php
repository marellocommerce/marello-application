<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Form\Type\WarehouseType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WarehouseController extends AbstractController
{
    /**
     * @Route(
     *     path="/update-default",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_warehouse_updatedefault"
     * )
     * @Template("@MarelloInventory/Warehouse/updateDefault.html.twig")
     * @AclAncestor("marello_inventory_warehouse_update")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateDefaultAction(Request $request)
    {
        $aclHelper = $this->container->get(AclHelper::class);
        $entity = $this->container->get(ManagerRegistry::class)
            ->getRepository(Warehouse::class)
            ->getDefault($aclHelper);

        $form = $this->createForm(WarehouseType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->container->get(ManagerRegistry::class)->getManager();
            $em->persist($entity);
            $em->flush();
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.inventory.messages.success.warehouse.saved')
            );
            return $this->redirectToRoute('marello_inventory_warehouse_updatedefault');
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                AclHelper::class,
                ManagerRegistry::class,
                TranslatorInterface::class,
                ManagerRegistry::class
            ]
        );
    }
}
