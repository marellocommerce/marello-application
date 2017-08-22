<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ProductSupplierRelationTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new ProductSupplierRelation(), [
            ['id', 42],
            ['product', new Product()],
            ['supplier', new Supplier()],
            ['quantityOfUnit', 42],
            ['priority', 42],
            ['cost', 'some string'],
            ['canDropship', 1]
        ]);
    }
}
