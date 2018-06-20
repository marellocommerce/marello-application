<?php
namespace Marello\Bundle\MagentoBundle\Tests\Unit\Entity;

use Marello\Bundle\MagentoBundle\Entity\Category;

class CategoryTest extends AbstractEntityTestCase
{
    const TEST_CATALOG_CODE = 'ccode';
    const TEST_CATALOG_NAME = 'wname';
    
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return Category::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        return [
            'id'   => ['id', self::TEST_ID, self::TEST_ID],
            'code' => ['code', self::TEST_CATALOG_CODE, self::TEST_CATALOG_CODE],
            'name' => ['name', self::TEST_CATALOG_NAME, self::TEST_CATALOG_NAME]
        ];
    }
}
