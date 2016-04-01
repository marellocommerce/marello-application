<?php

namespace Marello\Bundle\PurchaseOrderBundle\Controller;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $handler = $this->get("marello_purchase_order.form.handler.purchase_order_create");

        $form = $handler->getForm();

        if ($handler->handle($products)) {
            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_view',
                    'parameters' => [
                        'id' => $form->getData()->getId(),
                    ],
                ],
                [
                    'route'      => 'marello_purchaseorder_purchaseorder_index',
                    'parameters' => [],
                ]
            );
        }

        return [
            'products' => $products,
            'form' => $form->createView()
        ];
    }
}
