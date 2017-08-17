<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ProductStatusTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new ProductStatus('some name'), [
            ['label', 'some label'],
        ]);
    }
}
