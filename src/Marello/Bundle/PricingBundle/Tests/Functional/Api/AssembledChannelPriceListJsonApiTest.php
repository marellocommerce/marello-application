<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Api;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

class AssembledChannelPriceListJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'assembledchannelpricelists';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadProductChannelPricingData::class
        ]);
    }

    /**
     * Test cget a list of assembled priceslists
     */
    public function testGetListOfAssembledChannelPriceLists()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(5, $response);
        $this->dumpYmlTemplate('cget_assembled_channel_price_list.yml', $response);
        $this->assertResponseContains('cget_assembled_channel_price_list.yml', $response);
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
        $this->dumpYmlTemplate('cget_channel_pricelist_by_sku.yml', $response);
        $this->assertResponseContains('cget_channel_pricelist_by_sku.yml', $response);
    }

    /**
     * Test get pricelist filtered by product sku
     */
    public function testGetPriceListByChannel()
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['saleschannel' =>  $salesChannel->getCode() ]
            ]
        );
        $this->assertJsonResponse($response);
        $this->dumpYmlTemplate('cget_channel_pricelist_by_channel.yml', $response);
        $this->assertResponseContains('cget_channel_pricelist_by_channel.yml', $response);
    }

//    /**
//     * test create of new pricelist without a price
//     */
//    public function testCreateNewPriceListWithoutPrice()
//    {
//        $response = $this->post(
//            ['entity' => self::TESTING_ENTITY],
//            'assembledpricelist_without_price_create.yml',
//            [],
//            false
//        );
//
//        $this->assertJsonResponse($response);
//        $this->assertResponseStatusCodeEquals($response, Response::HTTP_INTERNAL_SERVER_ERROR);
//    }
//
//    /**
//     * test create of new pricelist with a price
//     */
    public function testCreateNewPriceListWithDefaultPrice()
    {
//        $productResponse =  $this->post(
//            ['entity' => 'products'],
//            'product_without_prices.yml'
//        );
//        $this->assertJsonResponse($productResponse);
//
//        $response = $this->post(
//            ['entity' => self::TESTING_ENTITY],
//            'assembledpricelist_create.yml'
//        );
//
//        $this->assertJsonResponse($response);
//        $responseContent = json_decode($response->getContent());
//        /** @var AssembledPriceList $assembledPriceList */
//        $assembledPriceList = $this->getEntityManager()->find(AssembledPriceList::class, $responseContent->data->id);
//        $this->assertEquals(
//            $assembledPriceList->getDefaultPrice()->getValue(),
//            $responseContent->included[0]->attributes->value
//        );
//
//        $responseContent = json_decode($productResponse->getContent());
//        /** @var Product $product */
//        $productRepo = $this->getEntityManager()->getRepository(Product::class);
//        $product = $productRepo->findOneBySku($responseContent->data->id);
//        $this->assertCount(1, $product->getPrices());
    }
}
