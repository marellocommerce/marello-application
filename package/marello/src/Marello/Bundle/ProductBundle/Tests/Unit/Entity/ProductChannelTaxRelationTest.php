<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;

class ProductChannelTaxRelationTest extends TestCase
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
