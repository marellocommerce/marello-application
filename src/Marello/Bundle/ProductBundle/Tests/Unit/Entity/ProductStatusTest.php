<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class ProductStatusTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new ProductStatus('some name'), [
            ['label', 'some label'],
        ]);
    }
}
