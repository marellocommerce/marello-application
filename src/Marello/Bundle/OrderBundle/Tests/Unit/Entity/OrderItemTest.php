<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Entity;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class OrderItemTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new OrderItem(), [
            ['id', 42],
            ['product', new Product()],
            ['productName', 'some string'],
            ['productSku', 'some string'],
            ['order', new Order()],
            ['quantity', 42],
            ['price', 42],
            ['originalPriceInclTax', 42],
            ['originalPriceExclTax', 42],
            ['purchasePriceIncl', 42],
            ['tax', 42],
            ['taxPercent', 3.1415926],
            ['discountPercent', 3.1415926],
            ['discountAmount', 'some string'],
            ['rowTotalInclTax', 42],
            ['rowTotalExclTax', 42],
            ['taxCode', new TaxCode()]
        ]);
        $this->assertPropertyCollections(new OrderItem(), [
            ['returnItems', new ReturnItem()],
        ]);
    }
}
