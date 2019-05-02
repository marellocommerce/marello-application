<?php

namespace Marello\Bundle\PackingBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;

class PackingSlipItemTest extends TestCase
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
