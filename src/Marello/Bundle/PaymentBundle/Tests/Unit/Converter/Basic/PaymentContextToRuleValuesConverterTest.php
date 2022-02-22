<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Converter\Basic;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecoratorFactory;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Doctrine\DoctrinePaymentLineItemCollection;
use Marello\Bundle\PaymentBundle\Context\PaymentContext;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Marello\Bundle\PaymentBundle\Context\Converter\Basic\BasicPaymentContextToRulesValueConverter;
use Marello\Bundle\PaymentBundle\ExpressionLanguage\DecoratedProductLineItemFactory;
use Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingAddressStub;
use Oro\Component\Testing\Unit\EntityTrait;

class PaymentContextToRuleValuesConverterTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var DecoratedProductLineItemFactory
     */
    protected $factory;

    /**
     * @var BasicPaymentContextToRulesValueConverter
     */
    protected $shippingContextToRuleValuesConverter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new DecoratedProductLineItemFactory(
            $this->createMock(VirtualFieldsProductDecoratorFactory::class)
        );

        $this->shippingContextToRuleValuesConverter = new BasicPaymentContextToRulesValueConverter(
            $this->factory
        );
    }

    /**
     * @dataProvider convertDataProvider
     * @param PaymentContext $context
     */
    public function testConvert(PaymentContext $context)
    {
        $expectedValues = [
            'lineItems' => array_map(function (PaymentLineItem $lineItem) use ($context) {
                return $this->factory
                    ->createLineItemWithDecoratedProductByLineItem($context->getLineItems()->toArray(), $lineItem);
            }, $context->getLineItems()->toArray()),
            'shippingOrigin' => $context->getShippingOrigin(),
            'billingAddress' => $context->getBillingAddress(),
            'shippingAddress' => $context->getShippingAddress(),
            'paymentMethod' => $context->getPaymentMethod(),
            'currency' => $context->getCurrency(),
            'subtotal' => $context->getSubtotal(),
            'customer' => $context->getCustomer(),
            'company'  => $context->getCompany(),
            'total'    => $context->getTotal()
        ];
        $this->assertEquals($expectedValues, $this->shippingContextToRuleValuesConverter->convert($context));
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            [
                'context' => new PaymentContext([
                    PaymentContext::FIELD_LINE_ITEMS => new DoctrinePaymentLineItemCollection([
                        new PaymentLineItem([
                            PaymentLineItem::FIELD_PRODUCT => $this->getEntity(Product::class, ['id' => 1]),
                        ]),
                    ]),
                    PaymentContext::FIELD_SHIPPING_ORIGIN => $this->getEntity(ShippingAddressStub::class, [
                        'region' => $this->getEntity(Region::class, [
                            'code' => 'CA',
                        ], ['US-CA']),
                    ]),
                    PaymentContext::FIELD_BILLING_ADDRESS => $this->getEntity(ShippingAddressStub::class, [
                        'country' => new Country('US'),
                    ]),
                    PaymentContext::FIELD_SHIPPING_ADDRESS => $this->getEntity(ShippingAddressStub::class, [
                        'country' => new Country('US'),
                        'region' => $this->getEntity(Region::class, [
                            'code' => 'CA',
                        ], ['US-CA']),
                        'postalCode' => '90401',
                    ]),
                    PaymentContext::FIELD_PAYMENT_METHOD => 'integration_payment_method',
                    PaymentContext::FIELD_CURRENCY => 'USD',
                    PaymentContext::FIELD_SUBTOTAL => Price::create(10.0, 'USD'),
                    PaymentContext::FIELD_CUSTOMER => (new Customer())->setFirstName('Customer Name'),
                ]),
            ],
        ];
    }
}
