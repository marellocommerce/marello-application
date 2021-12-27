<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\Builder\Basic;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Marello\Bundle\PaymentBundle\Context\Builder\Basic\BasicPaymentContextBuilder;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContext;

class BasicPaymentContextBuilderTest extends \PHPUnit\Framework\TestCase
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
    /**
     * @var PaymentLineItemCollectionFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentLineItemCollectionFactory;

    protected function setUp(): void
    {
        $this->customerMock = $this->createMock(Customer::class);
        $this->lineItemsCollectionMock = $this->createMock(PaymentLineItemCollectionInterface::class);
        $this->lineItemsCollectionMock
            ->expects(static::any())
            ->method('toArray')
            ->willReturn([$this->createMock(PaymentLineItemInterface::class)]);
        $this->billingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingAddressMock = $this->createMock(AddressInterface::class);
        $this->shippingOriginMock = $this->createMock(AddressInterface::class);
        $this->subtotalMock = $this->createMock(Price::class);
        $this->sourceEntityMock = $this->createMock(Order::class);
        $this->shippingOriginMock = $this->createMock(AddressInterface::class);
        $this->paymentLineItemCollectionFactory = $this->createMock(PaymentLineItemCollectionFactoryInterface::class);
        $this->paymentLineItemCollectionFactory
            ->expects(static::any())
            ->method('createPaymentLineItemCollection')
            ->willReturn($this->lineItemsCollectionMock);
    }

    public function testFullContextBuilding()
    {
        $paymentMethod = 'paymentMethod';
        $currency = 'usd';
        $entityId = '12';

        $builder = new BasicPaymentContextBuilder(
            $this->sourceEntityMock,
            $entityId,
            $this->paymentLineItemCollectionFactory
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

        $builder = new BasicPaymentContextBuilder(
            $this->sourceEntityMock,
            $entityId,
            $this->paymentLineItemCollectionFactory
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

        $builder = new BasicPaymentContextBuilder(
            $this->sourceEntityMock,
            $entityId,
            $this->paymentLineItemCollectionFactory
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
     * @return PaymentContext
     */
    private function getExpectedFullContext($paymentMethod, $currency, $entityId, AddressInterface $shippingOrigin = null)
    {
        $params = [
            PaymentContext::FIELD_CUSTOMER => $this->customerMock,
            PaymentContext::FIELD_LINE_ITEMS => $this->lineItemsCollectionMock,
            PaymentContext::FIELD_BILLING_ADDRESS => $this->billingAddressMock,
            PaymentContext::FIELD_SHIPPING_ADDRESS => $this->shippingAddressMock,
            PaymentContext::FIELD_SHIPPING_ORIGIN => $shippingOrigin,
            PaymentContext::FIELD_PAYMENT_METHOD => $paymentMethod,
            PaymentContext::FIELD_CURRENCY => $currency,
            PaymentContext::FIELD_SUBTOTAL => $this->subtotalMock,
            PaymentContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            PaymentContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];
        $params = array_diff_key($params, array_filter($params, 'is_null'));
        
        return new PaymentContext($params);
    }

    /**
     * @param int              $entityId
     * @param AddressInterface $shippingOrigin
     *
     * @return PaymentContext
     */
    private function getExpectedContextWithoutOptionalFields($entityId, AddressInterface $shippingOrigin)
    {
        $params = [
            PaymentContext::FIELD_LINE_ITEMS => $this->lineItemsCollectionMock,
            PaymentContext::FIELD_SHIPPING_ORIGIN => $shippingOrigin,
            PaymentContext::FIELD_SOURCE_ENTITY => $this->sourceEntityMock,
            PaymentContext::FIELD_SOURCE_ENTITY_ID => $entityId,
        ];
        $params = array_diff_key($params, array_filter($params, 'is_null'));
        
        return new PaymentContext($params);
    }
}
