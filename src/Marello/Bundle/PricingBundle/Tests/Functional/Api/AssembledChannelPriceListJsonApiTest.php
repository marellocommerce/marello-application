<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

class AssembledChannelPriceListJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloassembledchannelpricelists';

    protected function setUp(): void
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
        $this->markTestSkipped('issue with product load');
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(5, $response);
        $this->assertResponseContains('cget_assembled_channel_price_list.yml', $response);
    }

    /**
     * Test get pricelist filtered by product sku
     */
    public function testGetPriceListByProductSku()
    {
        $this->markTestSkipped('issue with product load');
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['product' =>  $product->getSku() ]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('cget_channel_pricelist_by_sku.yml', $response);
    }

    /**
     * Test get pricelist filtered by product sku
     */
    public function testGetPriceListByChannel()
    {
        $this->markTestSkipped('issue with product load');
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['saleschannel' =>  $salesChannel->getCode() ]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('cget_channel_pricelist_by_channel.yml', $response);
    }

    /**
     * test create of new pricelist with a price
     */
    public function testCreateNewPriceListWithPrices()
    {
        $this->markTestSkipped('issue with product load');
        $productResponse =  $this->post(
            ['entity' => 'marelloproducts'],
            'product_without_prices.yml'
        );
        $this->assertJsonResponse($productResponse);

        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'assembledchannelpricelist_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());
        /** @var AssembledChannelPriceList $assembledChannelPriceList */
        $assembledChannelPriceList = $this->getEntityManager()->find(
            AssembledChannelPriceList::class,
            $responseContent->data->id
        );
        $this->assertEquals(
            $assembledChannelPriceList->getDefaultPrice()->getValue(),
            $responseContent->included[0]->attributes->value
        );

        $this->assertEquals(
            $assembledChannelPriceList->getSpecialPrice()->getValue(),
            $responseContent->included[1]->attributes->value
        );

        $responseContent = json_decode($productResponse->getContent());
        /** @var Product $product */
        $productRepo = $this->getEntityManager()->getRepository(Product::class);
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->getContainer()->get('oro_security.acl_helper');
        $product = $productRepo->findOneBySku($responseContent->data->id, $aclHelper);
        $this->assertCount(1, $product->getChannelPrices());
    }
}
