<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Converter\Basic;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecoratorFactory;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\ShippingBundle\Converter\Basic\ShippingContextToRulesValuesConverter;
use Marello\Bundle\ShippingBundle\ExpressionLanguage\DecoratedProductLineItemFactory;
use Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingAddressStub;
use Oro\Component\Testing\Unit\EntityTrait;

class ShippingContextToRuleValuesConverterTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var DecoratedProductLineItemFactory
     */
    protected $factory;

    /**
     * @var ShippingContextToRulesValuesConverter
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

        $this->shippingContextToRuleValuesConverter = new ShippingContextToRulesValuesConverter(
            $this->factory
        );
    }

    /**
     * @dataProvider convertDataProvider
     * @param ShippingContext $context
     */
    public function testConvert(ShippingContext $context)
    {
        $expectedValues = [
            'lineItems' => array_map(function (ShippingLineItem $lineItem) use ($context) {
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
                'context' => new ShippingContext([
                    ShippingContext::FIELD_LINE_ITEMS => new DoctrineShippingLineItemCollection([
                        new ShippingLineItem([
                            ShippingLineItem::FIELD_PRODUCT => $this->getEntity(Product::class, ['id' => 1]),
                        ]),
                    ]),
                    ShippingContext::FIELD_SHIPPING_ORIGIN => $this->getEntity(ShippingAddressStub::class, [
                        'region' => $this->getEntity(Region::class, [
                            'code' => 'CA',
                        ], ['US-CA']),
                    ]),
                    ShippingContext::FIELD_BILLING_ADDRESS => $this->getEntity(ShippingAddressStub::class, [
                        'country' => new Country('US'),
                    ]),
                    ShippingContext::FIELD_SHIPPING_ADDRESS => $this->getEntity(ShippingAddressStub::class, [
                        'country' => new Country('US'),
                        'region' => $this->getEntity(Region::class, [
                            'code' => 'CA',
                        ], ['US-CA']),
                        'postalCode' => '90401',
                    ]),
                    ShippingContext::FIELD_PAYMENT_METHOD => 'integration_payment_method',
                    ShippingContext::FIELD_CURRENCY => 'USD',
                    ShippingContext::FIELD_SUBTOTAL => Price::create(10.0, 'USD'),
                    ShippingContext::FIELD_CUSTOMER => (new Customer())->setFirstName('Customer Name'),
                ]),
            ],
        ];
    }
}
