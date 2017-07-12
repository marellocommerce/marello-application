<?php

namespace Marello\Bundle\PricingBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PricingController extends Controller
{
    /**
     * @Config\Route("/get-currency-by-channel", name="marello_pricing_currency_by_channel")
     * @Config\Method({"GET"})
     * @Security\AclAncestor("marello_sales_saleschannel_view")
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
