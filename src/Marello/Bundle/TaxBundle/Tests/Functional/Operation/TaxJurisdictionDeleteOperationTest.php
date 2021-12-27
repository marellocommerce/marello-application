<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRateData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData;

class TaxJurisdictionDeleteOperationTest extends ActionTestCase
{
    protected function setUp(): void
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
            TaxJurisdiction::class,
            'marello_tax_taxjurisdiction_index'
        );
    }
}
