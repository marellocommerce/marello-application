<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context\LineItem\Builder\Basic;

use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic\BasicPaymentLineItemBuilder;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Marello\Bundle\PaymentBundle\Tests\Unit\Context\AbstractPaymentLineItemTest;

class BasicPaymentLineItemBuilderTest extends AbstractPaymentLineItemTest
{
    public function testFullBuild()
    {
        $builder = new BasicPaymentLineItemBuilder(
            self::TEST_QUANTITY,
            $this->productHolderMock
        );

        $builder
            ->setProduct($this->productMock)
            ->setPrice($this->priceMock)
            ->setProductSku(self::TEST_PRODUCT_SKU)
            ->setWeight(self::TEST_WEIGHT);

        $shippingLineItem = $builder->getResult();

        $expectedPaymentLineItem = new PaymentLineItem($this->getPaymentLineItemParams());

        $this->assertEquals($expectedPaymentLineItem, $shippingLineItem);
    }

    public function testOptionalBuild()
    {
        $builder = new BasicPaymentLineItemBuilder(
            self::TEST_QUANTITY,
            $this->productHolderMock
        );

        $shippingLineItem = $builder->getResult();

        $expectedPaymentLineItem = new PaymentLineItem([
            PaymentLineItem::FIELD_QUANTITY => self::TEST_QUANTITY,
            PaymentLineItem::FIELD_PRODUCT_HOLDER => $this->productHolderMock,
            PaymentLineItem::FIELD_ENTITY_IDENTIFIER => self::TEST_ENTITY_ID
        ]);

        $this->assertEquals($expectedPaymentLineItem, $shippingLineItem);
    }
}
