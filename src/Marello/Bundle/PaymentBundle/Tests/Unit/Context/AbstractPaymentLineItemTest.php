<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Marello\Bundle\ProductBundle\Entity\Product;

abstract class AbstractPaymentLineItemTest extends \PHPUnit\Framework\TestCase
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
    protected function getPaymentLineItemParams()
    {
        return [
            PaymentLineItem::FIELD_PRICE => $this->priceMock,
            PaymentLineItem::FIELD_QUANTITY => self::TEST_QUANTITY,
            PaymentLineItem::FIELD_PRODUCT_HOLDER => $this->productHolderMock,
            PaymentLineItem::FIELD_PRODUCT => $this->productMock,
            PaymentLineItem::FIELD_PRODUCT_SKU => self::TEST_PRODUCT_SKU,
            PaymentLineItem::FIELD_WEIGHT => self::TEST_WEIGHT,
            PaymentLineItem::FIELD_ENTITY_IDENTIFIER => self::TEST_ENTITY_ID,
        ];
    }
}
