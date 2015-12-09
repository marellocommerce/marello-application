<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class ProductControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    protected $productPostData = [

    ];

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        if (!isset($this->productPostData['owner'])) {
            $this->productPostData['owner'] = $this->getContainer()
                ->get('doctrine')
                ->getRepository('OroUserBundle:User')
                ->findOneBy(['username' => self::USER_NAME])->getId();
        }
    }

    public function testProductApiCreate()
    {
        $request = [
            'name' => 'New Product',
            'sku' => 'product123',
            'price' => 10,
            'inventoryItems' => [
                [
                    'sku' => 'product123',
                    'qty' => 100,
                    'warehouse' => 1,
                ]
            ],
            'owner' => $this->productPostData['owner']
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_product_api_post_product'),
            $request
        );

        $response = $this->getJsonResponseContent($this->client->getResponse(), 201);

        return $response['id'];
    }

    /**
     * @depends testProductApiCreate
     */
    public function testProductListCget()
    {
//        $this->client->request('GET', $this->getUrl('marello_product_api_get_products'));
//        $products = $this->getJsonResponseContent($this->client->getResponse(), 200);
//
//        $this->assertCount(1, $products);
//        $product = $products[0];
//
//        //tmp fix for letting "asserting equal" pass ...
//        unset($product['id']);
//        unset($product['organization']);
//
//
//        $this->assertProductDataEquals(
//            [
//                'name' => $this->productPostData['name'],
//                'sku' => $this->productPostData['sku'],
//                'price' => $this->productPostData['price'],
//                'stockLevel' => $this->productPostData['stockLevel'],
//                'owner' => 1,
//            ],
//            $product
//        );
    }

    /**
     * @depends testProductApiCreate
     * @param integer $id
     * @return array
     */
    public function testProductGet($id)
    {
        $this->client->request('GET', $this->getUrl('marello_product_api_get_product', ['id' => $id]));

        $product = $this->getJsonResponseContent($this->client->getResponse(), 200);

        var_dump($product);
        die();

        $this->assertProductDataEquals(
            [
                'name' => $this->productPostData['name'],
                'sku' => $this->productPostData['sku'],
                'price' => $this->productPostData['price'],
                'stockLevel' => $this->productPostData['stockLevel'],
                'owner' => 1,
            ],
            $product
        );

        return $product;
    }

    /**
     * @depends testProductGet
     * @param array $originalCase
     * @return integer
     */
    public function testProductPut(array $originalCase)
    {
        $id = $originalCase['id'];

        $putData = [
            'name' => 'Updated Name',
            'sku' => 'Updatedsku1234',
            'price' => 15,
            'stockLevel' => 150,
            'owner' => 1
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('marello_product_api_put_product', ['id' => $id]),
            $putData,
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('marello_product_api_get_product', ['id' => $id])
        );

        $updatedCase = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $expectedCase = array_merge($originalCase, $putData);

        $this->assertProductDataEquals($expectedCase, $updatedCase);

        return $id;
    }


    /**
     * @depends testProductPut
     * @param integer $id
     * @return integer
     */
    public function testProductDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('marello_product_api_delete_product', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl('marello_product_api_get_product', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    protected function assertProductDataEquals(array $expected, array $actual)
    {
        $this->assertArrayHasKey('name', $actual);
        $this->assertNotEmpty($actual['name']);

        $this->assertArrayHasKey('sku', $actual);
        $this->assertNotEmpty($actual['sku']);

        $this->assertArrayHasKey('price', $actual);
        $this->assertNotEmpty($actual['price']);

        $this->assertArrayHasKey('stockLevel', $actual);
        $this->assertNotEmpty($actual['stockLevel']);
        $this->assertInternalType('integer', $actual['stockLevel']);

        $this->assertArrayHasKey('owner', $actual);
        $this->assertNotEmpty($actual['owner']);
        $this->assertGreaterThan(0, $actual['owner']);
        $this->assertInternalType('integer', $actual['owner']);

        $this->assertArrayIntersectEquals($expected, $actual);
    }
}
