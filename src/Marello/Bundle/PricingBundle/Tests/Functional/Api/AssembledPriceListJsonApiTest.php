<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Api;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class AssembledPriceListJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloassembledpricelists';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    /**
     * Test cget a list of assembled priceslists
     */
    public function testGetListOfAssembledPriceLists()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(10, $response);
        $this->assertResponseContains('cget_assembled_price_list.yml', $response);
    }

    /**
     * Test get pricelist filtered by product sku
     */
    public function testGetPriceListByProductSku()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['product' =>  $product->getSku() ]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_pricelist_by_product_sku.yml', $response);
    }

    /**
     * test create of new pricelist without a price
     */
    public function testCreateNewPriceListWithoutPrice()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'assembledpricelist_without_price_create.yml',
            [],
            false
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * test create of new pricelist with a price
     */
    public function testCreateNewPriceListWithDefaultPrice()
    {
        $this->markTestSkipped('issue with product load');
        $productResponse =  $this->post(
            ['entity' => 'marelloproducts'],
            'product_without_prices.yml'
        );
        /** @var Product $product1 */
        $product1 = $this->getReference('product1');
        $this->assertJsonResponse($productResponse);

        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'assembledpricelist_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());
        /** @var AssembledPriceList $assembledPriceList */
        $assembledPriceList = $this->getEntityManager()->find(AssembledPriceList::class, $responseContent->data->id);
        $this->assertEquals(
            $assembledPriceList->getDefaultPrice()->getValue(),
            $responseContent->included[0]->attributes->value
        );

        $responseContent = json_decode($productResponse->getContent());
        /** @var Product $product */
        $productRepo = $this->getEntityManager()->getRepository(Product::class);
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $product = $productRepo->findOneBySku($responseContent->data->id, $aclHelper);
        $this->assertCount(1, $product->getPrices());
    }
}
