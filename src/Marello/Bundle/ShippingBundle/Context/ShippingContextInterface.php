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
     * @param ShippingLineItemCollectionInterface $lineItems
     * @return $this
     */
    public function setLineItems(ShippingLineItemCollectionInterface $lineItems);

    /**
     * @return MarelloAddress|null
     */
    public function getBillingAddress();

    /**
     * @param MarelloAddress|null $address
     * @return $this
     */
    public function setBillingAddress(MarelloAddress $address = null);

    /**
     * @return MarelloAddress
     */
    public function getShippingAddress();

    /**
     * @param MarelloAddress $address
     * @return $this
     */
    public function setShippingAddress(MarelloAddress $address);

    /**
     * @return MarelloAddress
     */
    public function getShippingOrigin();

    /**
     * @param MarelloAddress|null $address
     * @return $this
     */
    public function setShippingOrigin(MarelloAddress $address);

    /**
     * @return String|null
     */
    public function getPaymentMethod();

    /**
     * @param String|null $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * @return String|null
     */
    public function getCurrency();

    /**
     * @param String|null $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return Customer|null
     */
    public function getCustomer();

    /**
     * @param Customer|null $customer
     * @return $this
     */
    public function setCustomer(Customer $customer = null);

    /**
     * @return Price|null
     */
    public function getSubtotal();

    /**
     * @param Price|null $subtotal
     * @return $this
     */
    public function setSubtotal(Price $subtotal = null);

    /**
     * @return object
     */
    public function getSourceEntity();

    /**
     * @param object $sourceEntity
     * @return $this
     */
    public function setSourceEntity($sourceEntity);

    /**
     * @return mixed
     */
    public function getSourceEntityIdentifier();

    /**
     * @param mixed $sourceEntityIdentifier
     * @return $this
     */
    public function setSourceEntityIdentifier($sourceEntityIdentifier);
}
