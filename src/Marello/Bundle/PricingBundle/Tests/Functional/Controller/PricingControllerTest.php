<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Controller;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductChannelPricingData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class PricingControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadProductChannelPricingData::class,
        ]);
    }

    public function testGetProductPriceByChannelAvailable()
    {
        $queryData = [
            'salesChannel' => $this->getReference('marello_sales_channel_1')->getId(),
            'product_ids'  => [
                ['product' => $this->getReference('marello-product-0')->getId()],
                ['product' => $this->getReference('marello-product-1')->getId()],
                ['product' => $this->getReference('marello-product-2')->getId()],
                ['product' => $this->getReference('marello-product-3')->getId()],
                ['product' => $this->getReference('marello-product-4')->getId()],
            ],
        ];

        $this->client->request(
            'GET',
            $this->getUrl('marello_pricing_price_by_channel') . '?' . http_build_query($queryData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertCount(5, $responseData, 'Response should contain 5 results, one for each product requested.');
    }
}
