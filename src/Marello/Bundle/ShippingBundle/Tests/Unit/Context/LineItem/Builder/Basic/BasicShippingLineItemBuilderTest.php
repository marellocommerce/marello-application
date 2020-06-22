<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context\LineItem\Builder\Basic;

use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\BasicShippingLineItemBuilder;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\ShippingBundle\Tests\Unit\Context\AbstractShippingLineItemTest;

class BasicShippingLineItemBuilderTest extends AbstractShippingLineItemTest
{
    public function testFullBuild()
    {
        $builder = new BasicShippingLineItemBuilder(
            self::TEST_QUANTITY,
            $this->productHolderMock
        );

        $builder
            ->setProduct($this->productMock)
            ->setPrice($this->priceMock)
            ->setProductSku(self::TEST_PRODUCT_SKU)
            ->setWeight(self::TEST_WEIGHT);

        $shippingLineItem = $builder->getResult();

        $expectedShippingLineItem = new ShippingLineItem($this->getShippingLineItemParams());

        $this->assertEquals($expectedShippingLineItem, $shippingLineItem);
    }

    public function testOptionalBuild()
    {
        $builder = new BasicShippingLineItemBuilder(
            self::TEST_QUANTITY,
            $this->productHolderMock
        );

        $shippingLineItem = $builder->getResult();

        $expectedShippingLineItem = new ShippingLineItem([
            ShippingLineItem::FIELD_QUANTITY => self::TEST_QUANTITY,
            ShippingLineItem::FIELD_PRODUCT_HOLDER => $this->productHolderMock,
            ShippingLineItem::FIELD_ENTITY_IDENTIFIER => self::TEST_ENTITY_ID
        ]);

        $this->assertEquals($expectedShippingLineItem, $shippingLineItem);
    }
}
