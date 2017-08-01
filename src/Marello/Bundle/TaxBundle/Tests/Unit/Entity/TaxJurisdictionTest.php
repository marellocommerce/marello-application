<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class TaxJurisdictionTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new TaxJurisdiction(), [
            ['id', 42],
            ['code', 'some string'],
            ['description', 'some string'],
            ['country', new Country('ISO2')],
            ['region', new Region('Code')],
            ['regionText', 'some string'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAtSet', 1]
        ]);
        $this->assertPropertyCollections(new TaxJurisdiction(), [
            ['zipCodes', new ZipCode()],
        ]);
    }
}
