<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\Builder\Basic;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\ShippingBundle\Context\Builder\Basic\BasicShippingContextBuilder;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;

class BasicShippingContextBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Customer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    /**
     * @var ShippingLineItemCollectionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lineItemsCollectionMock;

    /**
     * @var AddressInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $billingAddressMock;

    /**
     * @var AddressInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingAddressMock;

    /**
     * @var AddressInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shippingOriginMock;

    /**
     * @var Price|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subtotalMock;

    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceEntityMock;

    protected function setUp(): void
    {
        $this->customerMock = $this->createMock(Customer::class);
        $this->lineItemsCollectionMock = $this->createMock(ShippingLineItemCollectionInterface::class);
        $this->billingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingOriginMock = $this->createMock(AddressInterface::class);
        $this->subtotalMock = $this->createMock(Price::class);
        $this->sourceEntityMock = $this->createMock(Order::class);
        $this->shippingOriginMock = $this->createMock(AddressInterface::class);
    }

    public function testFullContextBuilding()
    {
        $paymentMethod = 'paymentMethod';
        $currency = 'usd';
        $entityId = '12';

        $builder = new BasicShippingContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );

        $builder
            ->setCurrency($currency)
            ->setSubTotal($this->subtotalMock)
            ->setLineItems($this->lineItemsCollectionMock)
            ->setShippingAddress($this->shippingAddressMock)
            ->setBillingAddress($this->billingAddressMock)
            ->setCustomer($this->customerMock)
            ->setPaymentMethod($paymentMethod)
            ->setShippingOrigin($this->shippingOriginMock);

        $expectedContext = $this->getExpectedFullContext(
            $paymentMethod,
            $currency,
            $entityId,
            $this->shippingOriginMock
        );
        $context = $builder->getResult();

        $this->assertEquals($expectedContext, $context);
    }

    public function testOptionalFields()
    {
        $entityId = '12';

        $builder = new BasicShippingContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );
        $builder->setShippingOrigin($this->shippingOriginMock);

        $expectedContext = $this->getExpectedContextWithoutOptionalFields(
            $entityId,
            $this->shippingOriginMock
        );

        $context = $builder->getResult();

        $this->assertEquals($expectedContext, $context);
    }

    public function testWithoutOrigin()
    {
        $paymentMethod = 'paymentMethod';
        $currency = 'usd';
        $entityId = '12';

        $builder = new BasicShippingContextBuilder(
            $this->sourceEntityMock,
            $entityId
        );

        $builder
            ->setCurrency($currency)
            ->setSubTotal($this->subtotalMock)
            ->setLineItems($this->lineItemsCollectionMock)
            ->setShippingAddress($this->shippingAddressMock)
            ->setBillingAddress($this->billingAddressMock)
            ->setCustomer($this->customerMock)
            ->setPaymentMethod($paymentMethod);

        $expectedContext = $this->getExpectedFullContext(
            $paymentMethod,
            $currency,
            $entityId,
            null
        );
        $context = $builder->getResult();

        $this->assertEquals($expectedContext, $context);
    }

    /**
     * @param string           $paymentMethod
     * @param string           $currency
     * @param int              $entityId
     * @param AddressInterface|null $shippingOrigin
     *
     * @return ShippingContext
     */
    private function getExpectedFullContext($paymentMethod, $currency, $entityId, AddressInterface $shippingOrigin = null)
    {
        $params = [
            ShippingContext::FIELD_CUSTOMER => $this->customerMock,
            ShippingContext::FIELD_LINE_ITEMS => $this->lineItemsCollectionMock,
            ShippingContext::FIELD_BILLING_ADDRESS => $this->billingAddressMock,
            ShippingContext::FIELD_SHIPPING_ADDRESS => $this->shippingAddressMock,
            ShippingContext::FIELD_SHIPPING_ORIGIN => $shippingOrigin,
            ShippingContext::FIELD_PAYMENT_METHOD => $paymentMethod,
            ShippingContext::FIELD_CURRENCY => $currency,
            ShippingContext::FIELD_SUBTOTAL => $this->subtotalMock,
            ShippingContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            ShippingContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];

        return new ShippingContext($params);
    }

    /**
     * @param int              $entityId
     * @param AddressInterface $shippingOrigin
     *
     * @return ShippingContext
     */
    private function getExpectedContextWithoutOptionalFields($entityId, AddressInterface $shippingOrigin)
    {
        $params = [
            ShippingContext::FIELD_LINE_ITEMS => null,
            ShippingContext::FIELD_SHIPPING_ORIGIN => $shippingOrigin,
            ShippingContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            ShippingContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];

        return new ShippingContext($params);
    }
}
