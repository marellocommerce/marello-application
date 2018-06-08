<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Entity;

use Marello\Bundle\MagentoBundle\Entity\Website;

class WebsiteTest extends AbstractEntityTestCase
{
    const TEST_WEBSITE_CODE = 'wcode';
    const TEST_WEBSITE_NAME = 'wname';

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return Website::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        return [
            'id'   => ['id', self::TEST_ID, self::TEST_ID],
            'code' => ['code', self::TEST_WEBSITE_CODE, self::TEST_WEBSITE_CODE],
            'name' => ['name', self::TEST_WEBSITE_NAME, self::TEST_WEBSITE_NAME]
        ];
    }
}
