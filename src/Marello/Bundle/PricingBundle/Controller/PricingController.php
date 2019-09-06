<?php

namespace Marello\Bundle\PricingBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PricingController extends AbstractController
{
    /**
     * @Route(
     *     path="/get-currency-by-channel", 
     *     methods={"GET"},
     *     name="marello_pricing_currency_by_channel"
     * )
     * @AclAncestor("marello_sales_saleschannel_view")
     *
     * {@inheritdoc}
     */
    public function getCurrencyByChannelAction(Request $request)
    {
        return new JsonResponse(
            $this->get('marello_productprice.pricing.provider.currency_provider')->getCurrencyDataByChannel(
                $request->query->get('salesChannel')
            )
        );
    }
}
