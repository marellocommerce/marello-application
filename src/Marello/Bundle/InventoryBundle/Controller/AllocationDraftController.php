<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\AllocationDraft;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AllocationDraftController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_inventory_allocation_draft_index"
     * )
     * @Template("MarelloInventoryBundle:AllocationDraft:index.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => AllocationDraft::class
        ];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_inventory_allocation_draft_view"
     * )
     * @Template("MarelloInventoryBundle:AllocationDraft:view.html.twig")
     * @AclAncestor("marello_inventory_inventory_view")
     *
     * @param AllocationDraft $allocationDraft
     *
     * @return array
     */
    public function viewAction(AllocationDraft $allocationDraft)
    {
        return ['entity' => $allocationDraft];
    }
}
