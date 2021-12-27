<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;

use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRuleData;

class TaxRuleDeleteOperationTest extends ActionTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRuleData'
            ]
        );
    }

    public function testDelete()
    {
        $taxRule = $this->getReference(
            LoadTaxRuleData::REFERENCE_PREFIX . '.' . LoadTaxRuleData::TAX_RULE_1
        );

        $this->assertDeleteOperation(
            $taxRule->getId(),
            TaxRule::class,
            'marello_tax_taxrule_index'
        );
    }
}
