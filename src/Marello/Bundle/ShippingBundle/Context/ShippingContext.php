<?php

namespace Marello\Bundle\ShippingBundle\Context;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Symfony\Component\HttpFoundation\ParameterBag;

class ShippingContext extends ParameterBag implements ShippingContextInterface
{
    const FIELD_CUSTOMER = 'customer';
    const FIELD_LINE_ITEMS = 'line_items';
    const FIELD_BILLING_ADDRESS = 'billing_address';
    const FIELD_SHIPPING_ADDRESS = 'shipping_address';
    const FIELD_SHIPPING_ORIGIN = 'shipping_origin';
    const FIELD_PAYMENT_METHOD = 'payment_method';
    const FIELD_CURRENCY = 'currency';
    const FIELD_SUBTOTAL = 'subtotal';
    const FIELD_SOURCE_ENTITY = 'source_entity';
    const FIELD_SOURCE_ENTITY_ID = 'source_entity_id';

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomer()
    {
        return $this->get(self::FIELD_CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->set(self::FIELD_CUSTOMER, $customer);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLineItems()
    {
        return $this->get(self::FIELD_LINE_ITEMS);
    }

    /**
     * @inheritDoc
     */
    public function setLineItems(ShippingLineItemCollectionInterface $lineItems)
    {
        $this->set(self::FIELD_LINE_ITEMS, $lineItems);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingAddress()
    {
        return $this->get(self::FIELD_BILLING_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setBillingAddress(MarelloAddress $address = null)
    {
        $this->set(self::FIELD_BILLING_ADDRESS, $address);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingAddress()
    {
        return $this->get(self::FIELD_SHIPPING_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setShippingAddress(MarelloAddress $address)
    {
        $this->set(self::FIELD_SHIPPING_ADDRESS, $address);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingOrigin()
    {
        return $this->get(self::FIELD_SHIPPING_ORIGIN);
    }

    /**
     * @inheritDoc
     */
    public function setShippingOrigin(MarelloAddress $address)
    {
        $this->set(self::FIELD_SHIPPING_ORIGIN, $address);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethod()
    {
        return $this->get(self::FIELD_PAYMENT_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod($paymentMethod = null)
    {
        $this->set(self::FIELD_PAYMENT_METHOD, $paymentMethod);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency()
    {
        return $this->get(self::FIELD_CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currency)
    {
        $this->set(self::FIELD_CURRENCY, $currency);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubtotal()
    {
        return $this->get(self::FIELD_SUBTOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setSubtotal(Price $subtotal = null)
    {
        $this->set(self::FIELD_SUBTOTAL, $subtotal);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceEntity()
    {
        return $this->get(self::FIELD_SOURCE_ENTITY);
    }

    /**
     * @inheritDoc
     */
    public function setSourceEntity($sourceEntity)
    {
        $this->set(self::FIELD_SOURCE_ENTITY, $sourceEntity);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceEntityIdentifier()
    {
        return $this->get(self::FIELD_SOURCE_ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSourceEntityIdentifier($sourceEntityIdentifier)
    {
        $this->set(self::FIELD_SOURCE_ENTITY_ID, $sourceEntityIdentifier);

        return $this;
    }
}
