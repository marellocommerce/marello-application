<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductChannelPricingData;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class ProductControllerTest extends WebTestCase
{

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadProductChannelPricingData::class,
        ]);
    }

    /**
     * @return \Marello\Bundle\InventoryBundle\Entity\Warehouse
     */
    protected function getDefaultWarehouse()
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloInventoryBundle:Warehouse')
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

        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('organization', $data);
        $this->assertArrayHasKey('prices', $data);
        $this->assertArrayHasKey('channels', $data);
        $this->assertArrayHasKey('inventory', $data);
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

        $this->assertCount(10, $content, '10 products should be returned.');

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
        $product = $this->getReference('marello-product-0');

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
//            'prices'    => [
//                [
//                    'currency' => $this->getReference('marello_sales_channel_1')->getCurrency(),
//                    'value' => 200.00
//                ]
//            ],
            'inventory' => [
                ['quantity' => 10, 'warehouse' => $this->getDefaultWarehouse()->getId()],
            ],
            'channels'  => [
                $this->getReference('marello_sales_channel_1')->getId(),
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
//        $this->assertCount(1, $product->getPrices());
//        $this->assertEquals(
//            reset($data['prices'])['currency'],
//            $product->getPrices()->first()->getCurrency()
//        );
//        $this->assertEquals(
//            reset($data['prices'])['value'],
//            $product->getPrices()->first()->getValue()
//        );
        $this->assertCount(1, $product->getInventoryItems());
        $this->assertEquals(
            reset($data['inventory'])['quantity'],
            $product->getInventoryItems()->first()->getQuantity()
        );
        $this->assertEquals(
            reset($data['inventory'])['warehouse'],
            $product->getInventoryItems()->first()->getWarehouse()->getId()
        );
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
//            'prices'    => [
//                [
//                    'currency' => $this->getReference('marello_sales_channel_1')->getCurrency(),
//                    'value' => 200.00
//                ]
//            ],
            'inventory' => [
                ['quantity' => 10, 'warehouse' => -5 /* wrong ID */],
            ],
            'channels'  => [
                $this->getReference('marello_sales_channel_1')->getId(),
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
     */
    public function testUpdate()
    {
        /** @var Product $product */
        $product = $this->getReference('marello-product-0');

        $data = [
            'name'      => 'New name of product',
            'sku'       => $product->getSku(),
//            'price'     => $product->getPrice(),
//            'prices'    => $product->getPrices()->map(function (ProductPrice $price) {
//                return ['currency' => $price->getCurrency(), 'value' => $price->getValue()];
//            })->toArray(),
            'status'    => $product->getStatus()->getName(),
            'inventory' => $product->getInventoryItems()->map(function (InventoryItem $item) {
                return ['quantity' => $item->getQuantity(), 'warehouse' => $item->getWarehouse()->getId()];
            })->toArray(),
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
     * Tests deleting Product. This should return HTTP No Content.
     */
    public function testDelete()
    {
        $productId = $this->getReference('marello-product-0')->getId();

        $this->client->request(
            'DELETE',
            $this->getUrl('marello_product_api_delete_product', ['id' => $productId])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * Tests deleting a non-existent Product. This should return HTTP Not Found.
     */
    public function testDeleteNonExistent()
    {
        $productId = $this->getReference('marello-product-0')->getId() - 1;

        $this->client->request(
            'DELETE',
            $this->getUrl('marello_product_api_delete_product', ['id' => $productId])
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
