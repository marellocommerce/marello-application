<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Model;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Model\Result;
use Marello\Bundle\TaxBundle\Model\Taxable;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class TaxableTest extends \PHPUnit_Framework_TestCase
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
