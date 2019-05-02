<?php

namespace Marello\Bundle\ShippingBundle\Context\Builder;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;

interface ShippingContextBuilderInterface
{
    /**
     * @return ShippingContextInterface
     */
    public function getResult();

    /**
     * @param AddressInterface $shippingOrigin
     *
     * @return self
     */
    public function setShippingOrigin(AddressInterface $shippingOrigin);

    /**
     * @param ShippingLineItemCollectionInterface $lineItemCollection
     *
     * @return self
     */
    public function setLineItems(ShippingLineItemCollectionInterface $lineItemCollection);

    /**
     * @param AddressInterface $shippingAddress
     *
     * @return self
     */
    public function setShippingAddress(AddressInterface $shippingAddress);

    /**
     * @param AddressInterface $billingAddress
     *
     * @return self
     */
    public function setBillingAddress(AddressInterface $billingAddress);

    /**
     * @param string $paymentMethod
     *
     * @return self
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * @param Customer $customer
     *
     * @return self
     */
    public function setCustomer(Customer $customer);

    /**
     * @param Price $subTotal
     *
     * @return self
     */
    public function setSubTotal(Price $subTotal);

    /**
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency($currency);
}
