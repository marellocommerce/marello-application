<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class ProductControllerTest extends WebTestCase
{

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductPricingData',
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

        $this->assertArrayHasKey('price', $data);
        $this->assertEquals($product->getPrice(), $data['price']);

        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('organization', $data);
        $this->assertArrayHasKey('prices', $data);
        $this->assertArrayHasKey('channels', $data);
        $this->assertArrayHasKey('inventory', $data);
    }

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
            'price'     => 200.00,
            'status'    => 'enabled',
            'inventory' => [
                ['quantity' => 10, 'warehouse' => $this->getDefaultWarehouse()->getId()],
            ],
            'channels'  => [
                $this->getReference('marello_sales_channel_0')->getId(),
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
        $this->assertEquals($data['price'], $product->getPrice());
        $this->assertEquals($data['status'], $product->getStatus()->getName());
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

    public function testCreateWithInvalidData()
    {
        $data = [
            'name'      => 'New Product',
            'sku'       => 'NEW-SKU',
            'price'     => 200.00,
            'status'    => 'enabled',
            'inventory' => [
                ['quantity' => 10, 'warehouse' => -5 /* wrong ID */ ],
            ],
            'channels'  => [
                $this->getReference('marello_sales_channel_0')->getId(),
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

    // TODO: Test Update + Delete
}
