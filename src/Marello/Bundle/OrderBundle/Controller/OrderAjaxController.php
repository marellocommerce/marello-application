<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Oro\Bundle\SecurityBundle\Annotation as Security;

class OrderAjaxController extends Controller
{
    /**
     * @Config\Route("/get-order-item-data", name="marello_order_item_data")
     * @Config\Method({"GET"})
     * @Security\AclAncestor("marello_order_create")
     *
     * {@inheritdoc}
     */
    public function getOrderItemDataAction(Request $request)
    {
        return new JsonResponse(
            $this->get('marello_order.provider.order_item_data.composite')->getData(
                $request->query->get('salesChannel'),
                $request->query->get('product_ids', [])
            )
        );
    }
}
