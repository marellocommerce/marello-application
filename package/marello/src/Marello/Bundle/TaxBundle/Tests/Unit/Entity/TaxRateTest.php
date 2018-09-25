<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class TaxRateTest extends \PHPUnit_Framework_TestCase
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
