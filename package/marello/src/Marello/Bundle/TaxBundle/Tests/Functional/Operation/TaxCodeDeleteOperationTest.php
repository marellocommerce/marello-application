<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;

class TaxCodeDeleteOperationTest extends ActionTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData'
            ]
        );
    }

    public function testDelete()
    {
        $productTaxCode = $this->getReference(LoadTaxCodeData::TAXCODE_0_REF);

        $this->assertDeleteOperation(
            $productTaxCode->getId(),
            'marello_tax.taxcode.entity.class',
            'marello_tax_taxcode_index'
        );
    }
}
