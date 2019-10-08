<?php

namespace Marello\Bundle\PaymentBundle\Context\Builder\Basic;

use Marello\Bundle\CustomerBundle\Entity\Company;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Context\Builder\PaymentContextBuilderInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContext;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;

/**
 * Creates PaymentContext with needed parameters
 */
class BasicPaymentContextBuilder implements PaymentContextBuilderInterface
{
    /**
     * @var AddressInterface
     */
    private $shippingAddress;

    /**
     * @var AddressInterface
     */
    private $shippingOrigin;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Price
     */
    private $subTotal;

    /**
     * @var object
     */
    private $sourceEntity;

    /**
     * @var string
     */
    private $sourceEntityIdentifier;

    /**
     * @var array
     */
    private $lineItems = [];

    /**
     * @var AddressInterface
     */
    private $billingAddress;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Company
     */
    private $company;

    /**
     * @var PaymentLineItemCollectionFactoryInterface
     */
    private $paymentLineItemCollectionFactory;

    /**
     * @var float
     */
    private $total;

    /**
     * @param object                                    $sourceEntity
     * @param string                                    $sourceEntityIdentifier
     * @param PaymentLineItemCollectionFactoryInterface $paymentLineItemCollectionFactory
     */
    public function __construct(
        $sourceEntity,
        $sourceEntityIdentifier,
        PaymentLineItemCollectionFactoryInterface $paymentLineItemCollectionFactory
    ) {
        $this->sourceEntity = $sourceEntity;
        $this->sourceEntityIdentifier = $sourceEntityIdentifier;
        $this->paymentLineItemCollectionFactory = $paymentLineItemCollectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        $params = $this->getMandatoryParams();
        $params += $this->getOptionalParams();

        return new PaymentContext($params);
    }

    /**
     * {@inheritDoc}
     */
    public function setLineItems(PaymentLineItemCollectionInterface $lineItemCollection)
    {
        $this->lineItems = $lineItemCollection->toArray();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addLineItem(PaymentLineItemInterface $paymentLineItem)
    {
        $this->lineItems[] = $paymentLineItem;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingAddress(AddressInterface $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingOrigin(AddressInterface $shippingOrigin)
    {
        $this->shippingOrigin = $shippingOrigin;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSubTotal(Price $subTotal)
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return array
     */
    private function getMandatoryParams()
    {
        $lineItems = $this->paymentLineItemCollectionFactory->createPaymentLineItemCollection($this->lineItems);
        $params = [
            PaymentContext::FIELD_SOURCE_ENTITY => $this->sourceEntity,
            PaymentContext::FIELD_SOURCE_ENTITY_ID => $this->sourceEntityIdentifier,
            PaymentContext::FIELD_LINE_ITEMS => $lineItems,
        ];

        return $params;
    }

    /**
     * @return array
     */
    private function getOptionalParams()
    {
        $optionalParams = [
            PaymentContext::FIELD_CURRENCY => $this->currency,
            PaymentContext::FIELD_SUBTOTAL => $this->subTotal,
            PaymentContext::FIELD_BILLING_ADDRESS => $this->billingAddress,
            PaymentContext::FIELD_SHIPPING_ADDRESS => $this->shippingAddress,
            PaymentContext::FIELD_PAYMENT_METHOD => $this->paymentMethod,
            PaymentContext::FIELD_CUSTOMER => $this->customer,
            PaymentContext::FIELD_COMPANY => $this->company,
            PaymentContext::FIELD_SHIPPING_ORIGIN => $this->shippingOrigin,
            PaymentContext::FIELD_TOTAL => $this->total,
        ];

        // Exclude NULL elements.
        $optionalParams = array_diff_key($optionalParams, array_filter($optionalParams, 'is_null'));

        return $optionalParams;
    }
}
