<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReplenishmentOrderController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marelloenterprise_replenishmentorder_index"
     * )
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrder/index.html.twig")
     * @AclAncestor("marelloenterprise_replenishmentorder_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloEnterpriseReplenishmentBundle:ReplenishmentOrder'];
    }
    
    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marelloenterprise_replenishmentorder_view"
     * )
     * @Template("@MarelloEnterpriseReplenishment/ReplenishmentOrder/view.html.twig")
     * @AclAncestor("marelloenterprise_replenishmentorder_view")
     *
     * @param ReplenishmentOrder $order
     *
     * @return array
     */
    public function viewAction(ReplenishmentOrder $order)
    {
        return ['entity' => $order];
    }
}
