<?php

namespace Marello\Bundle\PricingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class PricingController extends Controller
{
    /**
     * @Config\Route("/get-currency-by-channel", name="marello_pricing_currency_by_channel")
     * @Config\Method({"GET"})
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
