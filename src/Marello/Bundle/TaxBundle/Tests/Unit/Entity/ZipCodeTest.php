<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class ZipCodeTest extends TestCase
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
