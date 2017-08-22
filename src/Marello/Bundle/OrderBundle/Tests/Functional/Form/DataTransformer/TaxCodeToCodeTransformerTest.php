<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Form\DataTransformer;

use Marello\Bundle\OrderBundle\Form\DataTransformer\TaxCodeToCodeTransformer;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class TaxCodeToCodeTransformerTest extends WebTestCase
{
    const TRANSFORMER_SERVICE_ID = 'marello_order.form.data_transformer.tax_code_to_code';

    /**
     * @var TaxCodeToCodeTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->initClient();

        $this->loadFixtures([
            LoadTaxCodeData::class,
        ]);

        $this->transformer = $this->client->getContainer()->get(self::TRANSFORMER_SERVICE_ID);
    }

    public function testTransform()
    {
        /** @var TaxCode $taxCode */
        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_0_REF);

        $result = $this->transformer->transform($taxCode);

        $this->assertEquals($taxCode->getCode(), $result);
    }

    public function testReverseTransformSuccess()
    {
        /** @var TaxCode $taxCode */
        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_0_REF);

        $result = $this->transformer->reverseTransform($taxCode->getCode());

        $this->assertEquals(
            $taxCode->getCode(),
            $result->getCode()
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformFail()
    {
        $wrongCode = 'this-is-wrong-code';
        $this->transformer->reverseTransform($wrongCode);
    }
}
