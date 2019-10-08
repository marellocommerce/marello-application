<?php

namespace Marello\Bundle\PaymentBundle\Context;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;

interface PaymentContextInterface
{
    /**
     * @return PaymentLineItemCollectionInterface
     */
    public function getLineItems();

    /**
     * @return AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * @return AddressInterface
     */
    public function getShippingAddress();

    /**
     * @return AddressInterface
     */
    public function getShippingOrigin();

    /**
     * @return string|null
     */
    public function getPaymentMethod();

    /**
     * @return string|null
     */
    public function getCurrency();

    /**
     * @return Customer|null
     */
    public function getCustomer();

    /**
     * @return Company|null
     */
    public function getCompany();
    
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

    /**
     * @return float
     */
    public function getTotal();
}
