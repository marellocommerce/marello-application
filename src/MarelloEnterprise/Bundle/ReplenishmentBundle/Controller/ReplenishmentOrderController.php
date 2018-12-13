<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ReplenishmentOrderController extends Controller
{
    /**
     * @Config\Route("/", name="marello_replenishment_order_index")
     * @Config\Template
     * @AclAncestor("marello_replenishment_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloEnterpriseReplenishmentBundle:ReplenishmentOrder'];
    }
    
    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_replenishment_order_view")
     * @Config\Template
     * @AclAncestor("marello_replenishment_order_view")
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