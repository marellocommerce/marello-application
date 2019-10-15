<?php

namespace Marello\Bundle\PaymentBundle\Tests\Unit\Context;

use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;

class PaymentLineItemTest extends AbstractPaymentLineItemTest
{
    public function testGetters()
    {
        $shippingLineItemParams = $this->getPaymentLineItemParams();

        $shippingLineItem = new PaymentLineItem($shippingLineItemParams);

        $this->assertEquals($shippingLineItemParams[PaymentLineItem::FIELD_PRICE], $shippingLineItem->getPrice());
        $this->assertEquals(
            $shippingLineItemParams[PaymentLineItem::FIELD_QUANTITY],
            $shippingLineItem->getQuantity()
        );
        $this->assertEquals(
            $shippingLineItemParams[PaymentLineItem::FIELD_PRODUCT_HOLDER],
            $shippingLineItem->getProductHolder()
        );
        $this->assertEquals($shippingLineItemParams[PaymentLineItem::FIELD_PRODUCT], $shippingLineItem->getProduct());
        $this->assertEquals(
            $shippingLineItemParams[PaymentLineItem::FIELD_PRODUCT_SKU],
            $shippingLineItem->getProductSku()
        );
        $this->assertEquals($shippingLineItemParams[PaymentLineItem::FIELD_WEIGHT], $shippingLineItem->getWeight());
        $this->assertEquals(
            $shippingLineItemParams[PaymentLineItem::FIELD_ENTITY_IDENTIFIER],
            $shippingLineItem->getEntityIdentifier()
        );
    }
}
