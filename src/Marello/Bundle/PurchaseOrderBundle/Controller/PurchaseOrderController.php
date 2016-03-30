<?php

namespace Marello\Bundle\PurchaseOrderBundle\Controller;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PurchaseOrderController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     * @Security\AclAncestor("marello_purchase_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => PurchaseOrder::class];
    }

    /**
     * @Config\Route("/select-products")
     * @Config\Template
     * @Security\AclAncestor("marello_purchase_order_create")
     */
    public function selectProductsAction()
    {
        return [];
    }
}
