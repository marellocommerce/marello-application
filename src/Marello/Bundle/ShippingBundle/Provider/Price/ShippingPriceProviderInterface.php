<?php

namespace Marello\Bundle\ShippingBundle\Provider\Price;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodViewCollection;

interface ShippingPriceProviderInterface
{
    /**
     * @param ShippingContextInterface $context
     *
     * @return ShippingMethodViewCollection
     */
    public function getApplicableMethodsViews(ShippingContextInterface $context);

    /**
     * @param ShippingContextInterface $context
     * @param string $methodId
     * @param string $typeId
     *
     * @return Price|null
     */
    public function getPrice(ShippingContextInterface $context, $methodId, $typeId);
}
