<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;

class ShippingContextTest extends \PHPUnit\Framework\TestCase
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
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lineItemsCollectionMock = $this->createMock(ShippingLineItemCollectionInterface::class);
        $this->billingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingOriginMock = $this->createMock(AddressInterface::class);
        $this->subtotalMock = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceEntityMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructionAndGetters()
    {
        $paymentMethod = 'paymentMethod';
        $currency = 'usd';
        $entityId = '12';

        $params = [
            ShippingContext::FIELD_CUSTOMER => $this->customerMock,
            ShippingContext::FIELD_LINE_ITEMS => $this->lineItemsCollectionMock,
            ShippingContext::FIELD_BILLING_ADDRESS => $this->billingAddressMock,
            ShippingContext::FIELD_SHIPPING_ADDRESS => $this->shippingAddressMock,
            ShippingContext::FIELD_SHIPPING_ORIGIN => $this->shippingOriginMock,
            ShippingContext::FIELD_PAYMENT_METHOD => $paymentMethod,
            ShippingContext::FIELD_CURRENCY => $currency,
            ShippingContext::FIELD_SUBTOTAL => $this->subtotalMock,
            ShippingContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            ShippingContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];

        $shippingContext = new ShippingContext($params);

        $getterValues = [
            ShippingContext::FIELD_CUSTOMER => $shippingContext->getCustomer(),
            ShippingContext::FIELD_LINE_ITEMS => $shippingContext->getLineItems(),
            ShippingContext::FIELD_BILLING_ADDRESS => $shippingContext->getBillingAddress(),
            ShippingContext::FIELD_SHIPPING_ADDRESS => $shippingContext->getShippingAddress(),
            ShippingContext::FIELD_SHIPPING_ORIGIN => $shippingContext->getShippingOrigin(),
            ShippingContext::FIELD_PAYMENT_METHOD => $shippingContext->getPaymentMethod(),
            ShippingContext::FIELD_CURRENCY => $shippingContext->getCurrency(),
            ShippingContext::FIELD_SUBTOTAL => $shippingContext->getSubtotal(),
            ShippingContext::FIELD_SOURCE_ENTITY => $shippingContext->getSourceEntity(),
            ShippingContext::FIELD_SOURCE_ENTITY_ID => $shippingContext->getSourceEntityIdentifier(),
        ];

        $this->assertEquals($params, $getterValues);
    }
}
