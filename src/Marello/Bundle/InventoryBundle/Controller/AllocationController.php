<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Marello\Bundle\InventoryBundle\Entity\Allocation;

class AllocationController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_inventory_allocation_index"
     * )
     * @Template("MarelloInventoryBundle:Allocation:index.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Allocation::class
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_allocation_view"
     * )
     * @Template("MarelloInventoryBundle:Allocation:view.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     *
     * @param Allocation $allocation
     *
     * @return array
     */
    public function viewAction(Allocation $allocation)
    {
        return ['entity' => $allocation];
    }
}
