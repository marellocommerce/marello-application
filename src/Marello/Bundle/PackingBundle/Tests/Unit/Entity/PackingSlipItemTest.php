<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\Entity;

use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class PackingSlipItemTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new PackingSlipItem(), [
            ['id', 42],
            ['packingSlip', new PackingSlip()],
            ['product', new Product()],
            ['productSku', 'some string'],
            ['productName', 'some string'],
            ['weight', 3.1415926],
            ['quantity', 3.1415926],
            ['comment', 'some string'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
    }
}
