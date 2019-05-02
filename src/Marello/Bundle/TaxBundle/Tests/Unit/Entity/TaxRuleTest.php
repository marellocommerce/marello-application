<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class TaxRuleTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new TaxRule(), [
            ['id', 42],
            ['taxCode', new TaxCode()],
            ['taxRate', new TaxRate()],
            ['taxJurisdiction', new TaxJurisdiction()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
    }
}
