<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Entity;

use Marello\Bundle\MagentoBundle\Entity\Product;

class ProductTest extends AbstractEntityTestCase
{
    const TEST_PRODUCT_SKU = 'product-sku';
    const TEST_PRODUCT_NAME = 'product-name';
    const TEST_PRODUCT_TYPE = 'simple';
    const TEST_PRODUCT_SPECIAL_PRICE = 9.99;
    const TEST_PRODUCT_PRICE = 10.99;

    /**
     * @inheritdoc
     */
    public function getEntityFQCN()
    {
        return Product::class;
    }

    /**
     * @inheritdoc
     */
    public function getSetDataProvider()
    {
        return [
            'id'   => ['id', self::TEST_ID, self::TEST_ID],
            'sku' => ['sku', self::TEST_PRODUCT_SKU, self::TEST_PRODUCT_SKU],
            'name' => ['sku', self::TEST_PRODUCT_NAME, self::TEST_PRODUCT_NAME],
            'type' => ['type', self::TEST_PRODUCT_TYPE, self::TEST_PRODUCT_TYPE],
            'special_price' => ['special_price', self::TEST_PRODUCT_SPECIAL_PRICE, self::TEST_PRODUCT_SPECIAL_PRICE],
            'price' => ['special_price', self::TEST_PRODUCT_PRICE, self::TEST_PRODUCT_PRICE],
        ];
    }
}
