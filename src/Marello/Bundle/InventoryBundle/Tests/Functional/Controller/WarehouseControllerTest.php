<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
    }

    public function testUpdateDefaultAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_inventory_warehouse_updatedefault')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
