<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class WarehouseControllerTest extends WebTestCase
{

    public function testSimpleTest()
    {
        $this->assertEquals(0, 0);
    }

}
