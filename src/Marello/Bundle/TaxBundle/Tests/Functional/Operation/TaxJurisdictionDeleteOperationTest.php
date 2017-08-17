<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Operation;

use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRateData;
use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;

class TaxJurisdictionDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData'
            ]
        );
    }

    public function testDelete()
    {
        $taxJurisdiction = $this->getReference(
            LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . LoadTaxRateData::CODE_1
        );

        $this->assertDeleteOperation(
            $taxJurisdiction->getId(),
            'marello_tax.taxjurisdiction.entity.class',
            'marello_tax_taxjurisdiction_index'
        );
    }
}
