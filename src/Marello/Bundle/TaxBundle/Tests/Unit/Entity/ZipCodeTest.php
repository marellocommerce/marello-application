<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ZipCodeTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new ZipCode(), [
            ['id', 42],
            ['zipCode', 'some string'],
            ['zipRangeStart', 'some string'],
            ['zipRangeEnd', 'some string'],
            ['taxJurisdiction', new TaxJurisdiction()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAtSet', 1]
        ]);
    }
}
