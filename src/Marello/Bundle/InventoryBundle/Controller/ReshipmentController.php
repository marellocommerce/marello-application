<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Form\Type\ReshipmentType;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReshipmentController extends AbstractController
{
    /**
     * @Route(
     *     path="/create/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_reshipment_create"
     * )
     * @Template("@MarelloInventory/Reshipment/create.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     *
     * @param Order $order
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Order $order, Request $request)
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $order,
            $this->createForm(ReshipmentType::class, $order),
            $this->container->get(TranslatorInterface::class)->trans('marello.inventory.messages.success.reshipment.saved'),
            $request,
            'marello_inventory.reshipment_form.handler'
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
