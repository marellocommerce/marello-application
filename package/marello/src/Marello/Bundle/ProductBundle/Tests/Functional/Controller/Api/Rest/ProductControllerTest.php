<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    /**
     * @return Warehouse
     */
    protected function getDefaultWarehouse()
    {
        return $this
            ->getContainer()
            ->get('marello_inventory.repository.warehouse')
            ->getDefault();
    }

    /**
     * Asserts product
     *
     * @param Product|null $product
     * @param array        $data
     */
    protected function assertProductApiResult(Product $product = null, array $data = [])
    {
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($product->getId(), $data['id']);

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals($product->getName(), $data['name']);

        $this->assertArrayHasKey('sku', $data);
        $this->assertEquals($product->getSku(), $data['sku']);

        $this->assertArrayHasKey('taxCode', $data);
        $this->assertArrayHasKey('salesChannelTaxCodes', $data);

        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('organization', $data);
        $this->assertArrayHasKey('prices', $data);
        $this->assertArrayHasKey('channels', $data);
        $this->assertArrayHasKey('inventoryItems', $data);
    }

    /**
     * Tests index of products. Should return HTTP OK.
     */
    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_product_api_get_products')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertCount(4, $content, '4 products should be returned.');

        foreach ($content as $item) {
            $product = $this->getContainer()
                ->get('doctrine')
                ->getRepository('MarelloProductBundle:Product')
                ->find($item['id']);
            $this->assertProductApiResult($product, $item);
        }
    }

    /**
     * Tests getting one product. Should return HTTP OK.
     */
    public function testGet()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $this->client->request(
            'GET',
            $this->getUrl('marello_product_api_get_product', ['id' => $product->getId()])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertProductApiResult($product, $content);
    }

    public function testCreate()
    {
        $data = [
            'name'      => 'New Product',
            'sku'       => 'NEW-SKU',
            'status'    => 'enabled',
            'channels'  => [
                $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId(),
            ],
            'prices'    => [
                [
                    'currency' => 'CHF',
                    'defaultPrice' => [ 'value' => 100.00 ],
                ]
            ],
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_product_api_post_product'),
            $data
        );

        $response = $this->client->getResponse();

        $content = json_decode($response->getContent(), true);

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $content);

        $product = $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloProductBundle:Product')
            ->find($content['id']);

        $this->assertEquals($data['name'], $product->getName());
        $this->assertEquals($data['sku'], $product->getSku());
        $this->assertEquals($data['status'], $product->getStatus()->getName());
        $this->assertCount(1, $product->getChannels());
        $this->assertEquals(reset($data['channels']), $product->getChannels()->first()->getId());
    }

    /**
     * Tests creating product with invalid data. Should return HTTP Bad Request.
     */
    public function testCreateWithInvalidData()
    {
        $data = [
            'name'      => 'New Product',
            'sku'       => 'NEW-SKU',
            'status'    => 'enabled',
            'inventoryItems' => [
                ['stock' => 10, 'warehouse' => -5 /* wrong ID */],
            ],
            'channels'  => [
                $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId(),
            ],
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_product_api_post_product'),
            $data
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Tests updating product. Should return HTTP No Content and update product in database.
     * @depends testCreate
     */
    public function testUpdate()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_2_REF);

        $data = [
            'name'      => 'New name of product',
            'sku'       => $product->getSku(),
            'status'    => $product->getStatus()->getName(),
            'channels'  => $product->getChannels()->map(function (SalesChannel $channel) {
                return $channel->getId();
            })->toArray(),
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('marello_product_api_put_product', ['id' => $product->getId()]),
            $data
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NO_CONTENT);

        $product = $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloProductBundle:Product')
            ->find($product->getId());

        $this->assertEquals($data['name'], $product->getName());
    }

    /**
     * Tests deleting a non-existent Product. This should return HTTP Not Found.
     */
    public function testDeleteNonExistent()
    {
        $productId = $this->getReference(LoadProductData::PRODUCT_1_REF)->getId() - 1;

        $this->client->request(
            'DELETE',
            $this->getUrl('marello_product_api_delete_product', ['id' => $productId])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
