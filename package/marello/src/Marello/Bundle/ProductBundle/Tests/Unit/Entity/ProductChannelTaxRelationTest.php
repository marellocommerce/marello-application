<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ProductChannelTaxRelationTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new ProductChannelTaxRelation(), [
            ['id', 42],
            ['product', new Product()],
            ['salesChannel', new SalesChannel()],
            ['taxCode', new TaxCode()]
        ]);
    }
}
