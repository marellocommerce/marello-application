<?php

namespace Marello\Bundle\ShippingBundle\Method;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;

interface ShippingMethodTypeInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @return string
     */
    public function getOptionsConfigurationFormType();

    /**
     * @param ShippingContextInterface $context
     * @param array $methodOptions
     * @param array $typeOptions
     * @return null|Price
     */
    public function calculatePrice(ShippingContextInterface $context, array $methodOptions, array $typeOptions);
    
    /**
     * @param ShippingContextInterface $context
     * @param string $method
     * @param string $type
     * @return Shipment|null
     */
    public function createShipment(ShippingContextInterface $context, $method, $type);
}
