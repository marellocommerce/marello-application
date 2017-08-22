<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Operation;

use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRuleData;
use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;

class TaxRuleDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
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
            'marello_tax.taxrule.entity.class',
            'marello_tax_taxrule_index'
        );
    }
}
