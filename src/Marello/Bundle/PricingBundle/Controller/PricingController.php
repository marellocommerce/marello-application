<?php

namespace Marello\Bundle\PricingBundle\Controller;

use Marello\Bundle\PricingBundle\Provider\CurrencyProvider;
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
            $this->container->get(CurrencyProvider::class)->getCurrencyDataByChannel(
                $request->query->get('salesChannel')
            )
        );
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CurrencyProvider::class,
            ]
        );
    }
}
