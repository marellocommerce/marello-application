<?php

namespace Marello\Bundle\PurchaseOrderBundle\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Config\Route("/create")
     * @Config\Template
     * @Security\AclAncestor("marello_purchase_order_create")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $products = $request->query->get('values', '');

        $products = array_map('intval', explode(',', $products));

        return [
            'products' => $products,
        ];
    }
}
