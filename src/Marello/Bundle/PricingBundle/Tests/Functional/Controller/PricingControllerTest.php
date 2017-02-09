<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Controller;

use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class PricingControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadSalesData::class,
            LoadProductData::class,
            LoadSupplierData::class,
            LoadProductChannelPricingData::class,
        ]);
    }

    public function testGetProductPriceByChannelAvailable()
    {
        $queryData = [
            'salesChannel' => $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId(),
            'product_ids'  => [
                ['product' => $this->getReference(LoadProductData::PRODUCT_1_REF)->getId()],
                ['product' => $this->getReference(LoadProductData::PRODUCT_2_REF)->getId()],
                ['product' => $this->getReference(LoadProductData::PRODUCT_3_REF)->getId()],
                ['product' => $this->getReference(LoadProductData::PRODUCT_4_REF)->getId()],
            ],
        ];

        $this->client->request(
            'GET',
            $this->getUrl('marello_pricing_price_by_channel') . '?' . http_build_query($queryData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertCount(4, $responseData, 'Response should contain 4 results, one for each product requested.');
    }
}
