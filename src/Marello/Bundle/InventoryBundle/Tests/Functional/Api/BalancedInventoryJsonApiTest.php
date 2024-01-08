<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;

class BalancedInventoryJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marellobalancedinventorylevels';

    protected function setUp(): void
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
        $this->assertResponseCount(10, $response);
        $this->assertResponseContains('cget_balancedinventory_list.yml', $response);
    }

    /**
     * Test filtering of inventory level by Product Sku
     */
    public function testFilterInventoryLevelByProductSku()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['product' =>  $product->getSku()]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_balancedinventory_by_product_sku.yml', $response);
    }


    /**
     * Test filtering of inventory level by SalesChannel
     */
    public function testFilterInventoryLevelBySalesChannel()
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['saleschannels' =>  $salesChannel->getCode()]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_balancedinventory_by_saleschannel.yml', $response);
    }

    /**
     * Test if we're not allowed to create a virtual inventory level via the API
     */
    public function testFailToCreateVirtualLevel()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'balancedinventorylevel_create.yml',
            [],
            false
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
