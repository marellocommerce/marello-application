<?php

namespace Marello\Bundle\PaymentBundle\Context\Builder;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;

/**
 * Provides an interface for payment context builder
 */
interface PaymentContextBuilderInterface
{
    /**
     * @return PaymentContextInterface
     */
    public function getResult();

    /**
     * @param PaymentLineItemCollectionInterface $lineItemCollection
     *
     * @return self
     */
    public function setLineItems(PaymentLineItemCollectionInterface $lineItemCollection);

    /**
     * @param PaymentLineItemInterface $paymentLineItem
     *
     * @return self
     */
    public function addLineItem(PaymentLineItemInterface $paymentLineItem);

    /**
     * @param AddressInterface $shippingAddress
     *
     * @return self
     */
    public function setShippingAddress(AddressInterface $shippingAddress);

    /**
     * @param AddressInterface $shippingOrigin
     *
     * @return self
     */
    public function setShippingOrigin(AddressInterface $shippingOrigin);

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
     * @param Company $company
     *
     * @return self
     */
    public function setCompany(Company $company);

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

    /**
     * @param float $total
     *
     * @return self
     */
    public function setTotal($total);
}
