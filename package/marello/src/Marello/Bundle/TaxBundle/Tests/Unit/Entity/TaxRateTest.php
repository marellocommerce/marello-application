<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

class TaxRateTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new TaxRate(), [
            ['id', 42],
            ['code', 'some string'],
            ['rate', 3.1415926]
        ]);
    }
}
