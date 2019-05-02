<?php

namespace Marello\Bundle\ShippingBundle\Checker;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class ShippingMethodEnabledByIdentifierChecker implements ShippingMethodEnabledByIdentifierCheckerInterface
{
    /**
     * @var ShippingMethodProviderInterface
     */
    private $shippingMethodProvider;

    /**
     * @param ShippingMethodProviderInterface $shippingMethodProvider
     */
    public function __construct(ShippingMethodProviderInterface $shippingMethodProvider)
    {
        $this->shippingMethodProvider = $shippingMethodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled($identifier)
    {
        return $this->shippingMethodProvider->getShippingMethod($identifier) !== null ?
            $this->shippingMethodProvider->getShippingMethod($identifier)->isEnabled() :
            false;
    }
}
