<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;

class InventoryLevelJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'inventorylevels';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadInventoryData::class
        ]);
    }

    /**
     * Test cget; Get a list of  virtual(balanced) inventory levels
     */
    public function testGetListOfInventoryLevels()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(4, $response);
        $this->dumpYmlTemplate('cget_inventory_list.yml', $response);
        $this->assertResponseContains('cget_inventory_list.yml', $response);
    }

    /**
     * Test if we're not allowed to create a virtual inventory level via the API
     */
    public function testFailToCreateInventoryLevel()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'inventorylevel_create.yml',
            [],
            false
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Test if we're not allowed to create a virtual inventory level via the API
     */
    public function testUpdateInventoryLevelRecord()
    {
        /** @var Product $existingProduct */
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => 1
            ],
            'inventorylevel_update.yml'
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
