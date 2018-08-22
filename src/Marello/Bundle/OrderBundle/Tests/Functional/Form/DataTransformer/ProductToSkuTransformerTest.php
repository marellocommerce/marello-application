<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Form\DataTransformer;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Form\DataTransformer\ProductToSkuTransformer;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

/**
 * Test if transformer is properly configured.
 */
class ProductToSkuTransformerTest extends WebTestCase
{
    const TRANSFORMER_SERVICE_ID = 'marello_order.form.data_transformer.product_to_sku';

    /** @var ProductToSkuTransformer */
    protected $transformer;

    public function setUp()
    {
        $this->initClient();

        $this->loadFixtures([
            LoadProductData::class,
        ]);

        $this->transformer = $this->client->getContainer()->get(self::TRANSFORMER_SERVICE_ID);
    }

    /**
     * Tests if product is correctly transformed to SKU.
     */
    public function testTransform()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $result = $this->transformer->transform($product);

        $this->assertEquals($product->getSku(), $result, 'Product should be transformed to correct SKU.');
    }

    /**
     * Tests if reverse transform properly transforms to existing entity provided that SKU is correct.
     */
    public function testReverseTransformSuccess()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $result = $this->transformer->reverseTransform($product->getSku());

        $this->assertEquals(
            $product->getId(),
            $result->getId(),
            'Transformer should retrieve correct product based on SKU.'
        );
    }

    /**
     * Tests if reverse transform from SKU properly fails and throws TransformationFailedException.
     *
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformFail()
    {
        $wrongSKU = 'this-is-wrong-sku';
        $this->transformer->reverseTransform($wrongSKU);
    }
}
