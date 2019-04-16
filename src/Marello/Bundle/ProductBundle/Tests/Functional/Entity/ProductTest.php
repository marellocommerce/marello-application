<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Entity;

use Oro\Bundle\EntityConfigBundle\Config\AttributeConfigHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testProductCanUseAttributes()
    {
        /** @var AttributeConfigHelper $configProvider */
        $configProvider = $this->getContainer()->get('oro_entity_config.config.attributes_config_helper');
        static::assertTrue($configProvider->isEntityWithAttributes(Product::class));
    }
}
