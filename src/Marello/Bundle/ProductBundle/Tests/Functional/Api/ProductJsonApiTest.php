<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EntityBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class ProductJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloproducts';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadUser::class,
            LoadUserData::class,
            LoadBusinessUnit::class,
            LoadOrganization::class,
            LoadProductData::class
        ]);
    }

    /**
     * Test cget (getting a list of products) of Product entity
     */
//    public function testGetListOfProducts()
//    {
//        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);
//
//        $this->assertJsonResponse($response);
//        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
//        $this->assertResponseCount(6, $response);
//        $this->assertResponseContains('cget_product_list.yml', $response);
//    }

    /**
     * Test get product by id
     */
    public function testGetProductById()
    {
        /** @var Product $product */
        //$product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => 'p1'],
            []
        );
//        $this->assertJsonResponse($response);
//        $this->assertResponseContains('get_product_by_id.yml', $response);
    }

//    /**
//     * Get a single product by sku
//     */
//    public function testGetProductFilteredBySku()
//    {
//        /** @var Product $product */
//        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
//        $response = $this->cget(
//            ['entity' => self::TESTING_ENTITY],
//            [
//                'filter' => ['sku' =>  $product->getSku() ]
//            ]
//        );
//        $this->assertJsonResponse($response);
//        $this->assertResponseCount(1, $response);
//        $this->assertResponseContains('get_product_by_sku.yml', $response);
//    }
//
//    /**
//     * Test Create new Product
//     */
//    public function testCreateNewProduct()
//    {
//        $response = $this->post(
//            ['entity' => self::TESTING_ENTITY],
//            'product_create.yml'
//        );
//
//        $this->assertJsonResponse($response);
//        $responseContent = json_decode($response->getContent());
//
//        /** @var Product $product */
//        $productRepo = $this->getEntityManager()->getRepository(Product::class);
//        /** @var AclHelper $aclHelper */
//        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
//        $product = $productRepo->findOneBySku($responseContent->data->id, $aclHelper);
//        $this->assertEquals(
//            $product->getDenormalizedDefaultName(),
//            $responseContent->data->attributes->denormalizedDefaultName
//        );
//    }
//
//    /**
//     * test Update existing Product with new name
//     */
//    public function testUpdateProduct()
//    {
//        /** @var Product $existingProduct */
//        $existingProduct = $this->getReference(LoadProductData::PRODUCT_1_REF);
//        $response = $this->patch(
//            [
//                'entity' => self::TESTING_ENTITY,
//                'id' => $existingProduct->getSku()
//            ],
//            'product_update.yml'
//        );
//
//        $this->assertJsonResponse($response);
//        $responseContent = json_decode($response->getContent());
//
//        /** @var Product $product */
//        $productRepo = $this->getEntityManager()->getRepository(Product::class);
//        /** @var AclHelper $aclHelper */
//        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
//        $product = $productRepo->findOneBySku($responseContent->data->id, $aclHelper);
//        $this->assertEquals($product->getManufacturingCode(), $responseContent->data->attributes->manufacturingCode);
//        $this->assertEquals(
//            $product->getDenormalizedDefaultName(),
//            $responseContent->data->attributes->denormalizedDefaultName
//        );
//    }
}
