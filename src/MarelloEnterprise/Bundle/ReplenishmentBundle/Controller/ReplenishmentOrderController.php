<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;

class ReplenishmentOrderController extends Controller
{
    /**
     * @Config\Route("/", name="marelloenterprise_replenishmentorder_index")
     * @Config\Template
     * @AclAncestor("marelloenterprise_replenishmentorder_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloEnterpriseReplenishmentBundle:ReplenishmentOrder'];
    }
    
    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marelloenterprise_replenishmentorder_view")
     * @Config\Template
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
