<?php

namespace Marello\Bundle\UPSBundle\EventListener;

use Marello\Bundle\PackingBundle\Event\BeforePackingSlipCreationEvent;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class BeforePackingSlipCreationListener
{
    /**
     * @var ShippingMethodProviderInterface
     */
    private $upsShippingMerhodProvider;

    /**
     * @param ShippingMethodProviderInterface $upsShippingMerhodProvider
     */
    public function __construct(ShippingMethodProviderInterface $upsShippingMerhodProvider)
    {
        $this->upsShippingMerhodProvider = $upsShippingMerhodProvider;
    }

    /**
     * @param BeforePackingSlipCreationEvent $event
     * @throws \Exception
     */
    public function beforeCreation(BeforePackingSlipCreationEvent $event)
    {
        $order = $event->getOrder();
        $shippingMethodName = $order->getShippingMethod();
        if ($shippingMethodName && $this->upsShippingMerhodProvider->hasShippingMethod($shippingMethodName)) {
            foreach ($order->getItems() as $item) {
                /** @var Product $product */
                $product = $item->getProduct();
                if (!$product->getWeight()) {
                    throw new \Exception(
                        sprintf('Packing Slip can\'t be created because product %s added to order
                     does not have weight specified', $product->getSku())
                    );
                }
            }
        }
    }
}
