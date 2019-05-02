<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class TaxableTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new Taxable(), [
            ['identifier', 42],
            ['className', 'some string'],
            ['taxationAddress', new MarelloAddress()],
            ['taxCode', new TaxCode()],
            ['quantity', 3.1415926, false],
            ['price', 3.1415926, false],
            ['amount', 123.00, false],
            ['shippingCost', 3.1415926, false],
            ['result', new Result(), false],
            ['currency', 'USD']
        ]);
        $this->assertPropertyCollections(new Taxable(), [
            ['items', new Taxable()],
        ]);
    }
}
