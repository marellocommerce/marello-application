<?php

namespace Marello\Bundle\ShippingBundle\Context;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;

interface ShippingContextInterface
{
    /**
     * @return ShippingLineItemCollectionInterface
     */
    public function getLineItems();

    /**
     * @return MarelloAddress|null
     */
    public function getBillingAddress();

    /**
     * @return MarelloAddress
     */
    public function getShippingAddress();

    /**
     * @return MarelloAddress
     */
    public function getShippingOrigin();

    /**
     * @return String|null
     */
    public function getPaymentMethod();

    /**
     * @return String|null
     */
    public function getCurrency();

    /**
     * @return Customer|null
     */
    public function getCustomer();

    /**
     * @return Price|null
     */
    public function getSubtotal();

    /**
     * @return object
     */
    public function getSourceEntity();

    /**
     * @return mixed
     */
    public function getSourceEntityIdentifier();
}
