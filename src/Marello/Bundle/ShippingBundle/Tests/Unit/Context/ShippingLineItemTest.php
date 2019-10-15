<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Context;

use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;

class ShippingLineItemTest extends AbstractShippingLineItemTest
{
    public function testGetters()
    {
        $shippingLineItemParams = $this->getShippingLineItemParams();

        $shippingLineItem = new ShippingLineItem($shippingLineItemParams);

        $this->assertEquals($shippingLineItemParams[ShippingLineItem::FIELD_PRICE], $shippingLineItem->getPrice());
        $this->assertEquals(
            $shippingLineItemParams[ShippingLineItem::FIELD_QUANTITY],
            $shippingLineItem->getQuantity()
        );
        $this->assertEquals(
            $shippingLineItemParams[ShippingLineItem::FIELD_PRODUCT_HOLDER],
            $shippingLineItem->getProductHolder()
        );
        $this->assertEquals($shippingLineItemParams[ShippingLineItem::FIELD_PRODUCT], $shippingLineItem->getProduct());
        $this->assertEquals(
            $shippingLineItemParams[ShippingLineItem::FIELD_PRODUCT_SKU],
            $shippingLineItem->getProductSku()
        );
        $this->assertEquals($shippingLineItemParams[ShippingLineItem::FIELD_WEIGHT], $shippingLineItem->getWeight());
        $this->assertEquals(
            $shippingLineItemParams[ShippingLineItem::FIELD_ENTITY_IDENTIFIER],
            $shippingLineItem->getEntityIdentifier()
        );
    }
}
