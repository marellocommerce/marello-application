<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Doctrine\DoctrineShippingLineItemCollection;
use Marello\Bundle\ShippingBundle\Context\ShippingContext;
use Marello\Bundle\ShippingBundle\Context\ShippingContextCacheKeyGenerator;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingAddressStub;
use Oro\Component\Testing\Unit\EntityTrait;

class ShippingContextCacheKeyGeneratorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ShippingContextCacheKeyGenerator
     */
    protected $generator;

    public function setUp(): void
    {
        $this->generator = new ShippingContextCacheKeyGenerator();
    }

    /**
     * @param $params
     * @param ShippingContext|null $context
     *
     * @return ShippingContext
     */
    private function createContext($params, ShippingContext $context = null)
    {
        $actualParams = $params;

        if (null === $context) {
            $actualParams[ShippingContext::FIELD_LINE_ITEMS] = new DoctrineShippingLineItemCollection([]);
        } else {
            $actualParams = array_merge($context->all(), $actualParams);
        }

        return new ShippingContext($actualParams);
    }

    /**
     * @param array $lineItemsParams
     * @param ShippingContext|null $context
     *
     * @return ShippingContext
     */
    private function createContextWithLineItems(array $lineItemsParams, ShippingContext $context = null)
    {
        $lineItems = [];
        foreach ($lineItemsParams as $params) {
            $lineItems[] = new ShippingLineItem($params);
        }

        return $this->createContext(
            [
                ShippingContext::FIELD_LINE_ITEMS => new DoctrineShippingLineItemCollection($lineItems),
            ],
            $context
        );
    }

    public function testGenerateHashSimpleFields()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $this->assertEquals(crc32(''), $this->generator->generateKey($context1));
        $this->assertEquals(crc32(''), $this->generator->generateKey($context2));

        $context1 = $this->createContext([ShippingContext::FIELD_CURRENCY => 'USD'], $context1);
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_CURRENCY => 'EUR'], $context2);
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_CURRENCY => 'USD'], $context2);
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContext([ShippingContext::FIELD_PAYMENT_METHOD => 'payment_method'], $context1);
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext(
            [ShippingContext::FIELD_PAYMENT_METHOD => 'another_payment_method'],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_PAYMENT_METHOD => 'payment_method'], $context2);
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => new Price()], $context1);
        $this->assertHashEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => new Price()], $context2);
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => Price::create(10, 'USD')], $context1);
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => Price::create(11, 'USD')], $context2);
        $this->assertHashNotEquals($context1, $context2);
        $context1 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => Price::create(10, 'USD')], $context1);
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_SUBTOTAL => Price::create(10, 'USD')], $context2);
        $this->assertHashEquals($context1, $context2);
    }

    public function testGenerateHashBillingAddress()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $address1 = new ShippingAddressStub();
        $address2 = new ShippingAddressStub();

        $context1 = $this->createContext([ShippingContext::FIELD_BILLING_ADDRESS => $address1], $context1);
        $this->assertHashEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_BILLING_ADDRESS => $address2], $context2);
        $this->assertHashEquals($context1, $context2);

        $this->assertAddressesFieldAffectsHash($context1, $context2, $address1, $address2);
    }

    public function testGenerateHashShippingAddress()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $address1 = new ShippingAddressStub();
        $address2 = new ShippingAddressStub();

        $context1 = $this->createContext([ShippingContext::FIELD_SHIPPING_ADDRESS => $address1], $context1);
        $this->assertHashEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_SHIPPING_ADDRESS => $address2], $context2);
        $this->assertHashEquals($context1, $context2);

        $this->assertAddressesFieldAffectsHash($context1, $context2, $address1, $address2);
    }

    public function testGenerateHashShippingOrigin()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $address1 = new ShippingAddressStub();
        $address2 = new ShippingAddressStub();

        $context1 = $this->createContext([ShippingContext::FIELD_SHIPPING_ORIGIN => $address1], $context1);
        $this->assertHashEquals($context1, $context2);
        $context2 = $this->createContext([ShippingContext::FIELD_SHIPPING_ORIGIN => $address2], $context2);
        $this->assertHashEquals($context1, $context2);

        $this->assertAddressesFieldAffectsHash($context1, $context2, $address1, $address2);
    }

    /**
     * @param ShippingContext $context1
     * @param ShippingContext $context2
     * @param ShippingAddressStub $address1
     * @param ShippingAddressStub $address2
     */
    protected function assertAddressesFieldAffectsHash(
        ShippingContext $context1,
        ShippingContext $context2,
        ShippingAddressStub $address1,
        ShippingAddressStub $address2
    ) {
        $address1->setStreet('street');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setStreet('another_street');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setStreet('street');
        $this->assertHashEquals($context1, $context2);

        $address1->setStreet2('street2');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setStreet2('another_street2');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setStreet2('street2');
        $this->assertHashEquals($context1, $context2);

        $address1->setCity('city');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setCity('another_city');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setCity('city');
        $this->assertHashEquals($context1, $context2);

        $address1->setRegionText('region');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setRegionText('another_region');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setRegionText('region');
        $this->assertHashEquals($context1, $context2);

        $address1->setRegion((new Region(1))->setCode(1));
        $this->assertHashNotEquals($context1, $context2);
        $address2->setRegion((new Region(2))->setCode(2));
        $this->assertHashNotEquals($context1, $context2);
        $address2->setRegion((new Region(1))->setCode(1));
        $this->assertHashEquals($context1, $context2);

        $address1->setPostalCode('postal_code');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setPostalCode('another_postal_code');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setPostalCode('postal_code');
        $this->assertHashEquals($context1, $context2);

        $country1 = new Country('postal_code');
        $country2 = new Country('postal_code');

        $address1->setCountry($country1);
        $this->assertHashNotEquals($context1, $context2);
        $address2->setCountry(new Country('wrong_postal_code'));
        $this->assertHashNotEquals($context1, $context2);
        $address2->setCountry($country2);
        $this->assertHashEquals($context1, $context2);

        $country1->setName('postal_code');
        $this->assertHashNotEquals($context1, $context2);
        $country2->setName('another_postal_code');
        $this->assertHashNotEquals($context1, $context2);
        $country2->setName('postal_code');
        $this->assertHashEquals($context1, $context2);

        $country1->setIso3Code('code');
        $this->assertHashNotEquals($context1, $context2);
        $country2->setIso3Code('another_code');
        $this->assertHashNotEquals($context1, $context2);
        $country2->setIso3Code('code');
        $this->assertHashEquals($context1, $context2);

        $address1->setOrganization('organization');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setOrganization('another_organization');
        $this->assertHashNotEquals($context1, $context2);
        $address2->setOrganization('organization');
        $this->assertHashEquals($context1, $context2);
    }

    public function testGenerateHashLineItemsOrder()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $product1 = new Product();
        $product2 = new Product();

        $item1 = new ShippingLineItem([ShippingLineItem::FIELD_PRODUCT => $product1]);
        $item2 = new ShippingLineItem([ShippingLineItem::FIELD_PRODUCT => $product2]);

        $lineItems = new DoctrineShippingLineItemCollection([$item1, $item2]);
        $context1 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context1);
        $this->assertHashEquals($context1, $context2);

        $lineItems = new DoctrineShippingLineItemCollection([$item1]);
        $context1 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context1);
        $this->assertHashEquals($context1, $context2);
        $lineItems = new DoctrineShippingLineItemCollection([$item2]);
        $context2 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context2);
        $this->assertHashEquals($context1, $context2);

        $item1 = new ShippingLineItem(
            [ShippingLineItem::FIELD_PRODUCT => $product1, ShippingLineItem::FIELD_QUANTITY => 1]
        );
        $item2 = new ShippingLineItem(
            [ShippingLineItem::FIELD_PRODUCT => $product2, ShippingLineItem::FIELD_QUANTITY => 2]
        );

        $lineItems = new DoctrineShippingLineItemCollection([$item1, $item2]);
        $context1 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context1);
        $context2 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context2);
        $this->assertHashEquals($context1, $context2);
        $lineItems = new DoctrineShippingLineItemCollection([$item2, $item1]);
        $context2 = $this->createContext([ShippingContext::FIELD_LINE_ITEMS => $lineItems], $context2);
        $this->assertHashEquals($context1, $context2);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerateHashLineItems()
    {
        $context1 = $this->createContext([]);
        $context2 = $this->createContext([]);

        $product1 = new Product();
        $product2 = new Product();

        $context1 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $product1, ShippingLineItem::FIELD_QUANTITY => 1]],
            $context1
        );
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $product2]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);

        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $product2, ShippingLineItem::FIELD_QUANTITY => 2]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);

        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $product2, ShippingLineItem::FIELD_QUANTITY => 1]],
            $context2
        );
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRICE => Price::create(10, 'USD')]],
            $context1
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRICE => Price::create(11, 'USD')]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRICE => Price::create(10, 'EUR')]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRICE => Price::create(10, 'USD')]],
            $context2
        );
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $this->getEntity(Product::class, ['id' => 1])]],
            $context1
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $this->getEntity(Product::class, ['id' => 2])]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_PRODUCT => $this->getEntity(Product::class, ['id' => 1])]],
            $context2
        );
        $this->assertHashEquals($context1, $context2);

        $weight = 10;
        $context1 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_WEIGHT => $weight]],
            $context1
        );
        $this->assertHashNotEquals($context1, $context2);
        $weight = 12;
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_WEIGHT => $weight]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $weight = 10;
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_WEIGHT => $weight]],
            $context2
        );
        $this->assertHashEquals($context1, $context2);

        $context1 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_ENTITY_IDENTIFIER => 1]],
            $context1
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_ENTITY_IDENTIFIER => 2]],
            $context2
        );
        $this->assertHashNotEquals($context1, $context2);
        $context2 = $this->createContextWithLineItems(
            [[ShippingLineItem::FIELD_ENTITY_IDENTIFIER => 1]],
            $context2
        );
        $this->assertHashEquals($context1, $context2);
    }

    /**
     * @param ShippingContextInterface $context1
     * @param ShippingContextInterface $context2
     */
    protected function assertHashEquals(ShippingContextInterface $context1, ShippingContextInterface $context2)
    {
        $this->assertEquals($this->generator->generateKey($context1), $this->generator->generateKey($context2));
    }

    /**
     * @param ShippingContextInterface $context1
     * @param ShippingContextInterface $context2
     */
    protected function assertHashNotEquals(ShippingContextInterface $context1, ShippingContextInterface $context2)
    {
        $this->assertNotEquals($this->generator->generateKey($context1), $this->generator->generateKey($context2));
    }
}
