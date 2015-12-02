<?php

namespace Marello\Bundle\PricingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\ProductBundle\Entity\Product;

class PricingController extends Controller
{
    /**
     * @Route("/get-product-price-by-channel", name="marello_pricing_price_by_channel")
     * @Method({"GET"})
     * @AclAncestor("marello_product_view")
     *
     * {@inheritdoc}
     */
    public function getProductPriceByChannelAction(Request $request)
    {
        return new JsonResponse($this->get('marello_productprice.product.provider.product_price')->getPriceBySalesChannel(
            $request->get('salesChannel'),
            $request->get('product_ids', [])
        ));
    }
}


