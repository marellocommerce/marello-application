<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Entity;

use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Oro\Bundle\EntityConfigBundle\Config\AttributeConfigHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class ProductTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testProductCanUseAttributes()
    {
        /** @var AttributeConfigHelper $configProvider */
        $configProvider = $this->getContainer()->get('oro_entity_config.config.attributes_config_helper');
        static::assertTrue($configProvider->isEntityWithAttributes(Product::class));
    }

    /**
     * {@inheritdoc}
     */
    public function testProductIsAssignedDefaultAttributeFamily()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        static::assertSame(
            ProductFamilyBuilder::DEFAULT_FAMILY_CODE,
            $product->getAttributeFamily()->getCode()
        );
    }
}
