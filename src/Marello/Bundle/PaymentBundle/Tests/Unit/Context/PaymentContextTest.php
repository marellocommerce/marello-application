<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContext;

class PaymentContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Customer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    /**
     * @var PaymentLineItemCollectionInterface|\PHPUnit\Framework\MockObject\MockObject
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
        $this->lineItemsCollectionMock = $this->createMock(PaymentLineItemCollectionInterface::class);
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
            PaymentContext::FIELD_CUSTOMER => $this->customerMock,
            PaymentContext::FIELD_LINE_ITEMS => $this->lineItemsCollectionMock,
            PaymentContext::FIELD_BILLING_ADDRESS => $this->billingAddressMock,
            PaymentContext::FIELD_SHIPPING_ADDRESS => $this->shippingAddressMock,
            PaymentContext::FIELD_SHIPPING_ORIGIN => $this->shippingOriginMock,
            PaymentContext::FIELD_PAYMENT_METHOD => $paymentMethod,
            PaymentContext::FIELD_CURRENCY => $currency,
            PaymentContext::FIELD_SUBTOTAL => $this->subtotalMock,
            PaymentContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            PaymentContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];

        $shippingContext = new PaymentContext($params);

        $getterValues = [
            PaymentContext::FIELD_CUSTOMER => $shippingContext->getCustomer(),
            PaymentContext::FIELD_LINE_ITEMS => $shippingContext->getLineItems(),
            PaymentContext::FIELD_BILLING_ADDRESS => $shippingContext->getBillingAddress(),
            PaymentContext::FIELD_SHIPPING_ADDRESS => $shippingContext->getShippingAddress(),
            PaymentContext::FIELD_SHIPPING_ORIGIN => $shippingContext->getShippingOrigin(),
            PaymentContext::FIELD_PAYMENT_METHOD => $shippingContext->getPaymentMethod(),
            PaymentContext::FIELD_CURRENCY => $shippingContext->getCurrency(),
            PaymentContext::FIELD_SUBTOTAL => $shippingContext->getSubtotal(),
            PaymentContext::FIELD_SOURCE_ENTITY => $shippingContext->getSourceEntity(),
            PaymentContext::FIELD_SOURCE_ENTITY_ID => $shippingContext->getSourceEntityIdentifier(),
        ];

        $this->assertEquals($params, $getterValues);
    }
}
