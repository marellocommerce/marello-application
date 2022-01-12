<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;

abstract class AbstractShippingLineItemTest extends \PHPUnit\Framework\TestCase
{
    const TEST_QUANTITY = 15;
    const TEST_PRODUCT_SKU = 'someSku';
    const TEST_PRODUCT_ID = 1;
    const TEST_ENTITY_ID = 'someId';
    const TEST_WEIGHT = 1.00;

    /**
     * @var Price|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceMock;

    /**
     * @var OrderItem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productHolderMock;

    /**
     * @var Product
     */
    protected $productMock;

    public function setUp(): void
    {
        $this->priceMock = $this->createMock(Price::class);

        $this->productHolderMock = $this->createMock(OrderItem::class);

        $this->productHolderMock->method('getId')->willReturn(static::TEST_ENTITY_ID);

        $this->productMock = $this->createMock(Product::class);

        $this->productMock->method('getSku')->willReturn(static::TEST_PRODUCT_SKU);
        $this->productMock->method('getId')->willReturn(static::TEST_PRODUCT_ID);
    }

    /**
     * @return array
     */
    protected function getShippingLineItemParams()
    {
        return [
            ShippingLineItem::FIELD_PRICE => $this->priceMock,
            ShippingLineItem::FIELD_QUANTITY => self::TEST_QUANTITY,
            ShippingLineItem::FIELD_PRODUCT_HOLDER => $this->productHolderMock,
            ShippingLineItem::FIELD_PRODUCT => $this->productMock,
            ShippingLineItem::FIELD_PRODUCT_SKU => self::TEST_PRODUCT_SKU,
            ShippingLineItem::FIELD_WEIGHT => self::TEST_WEIGHT,
            ShippingLineItem::FIELD_ENTITY_IDENTIFIER => self::TEST_ENTITY_ID,
        ];
    }
}
