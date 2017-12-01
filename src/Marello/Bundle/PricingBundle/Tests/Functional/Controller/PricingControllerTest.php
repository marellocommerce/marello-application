<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

/**
 * @outputBuffering enabled
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
            LoadProductChannelPricingData::class
        ]);
    }

    public function testCurrencyByChannel()
    {
        $channelId = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $currencyDataKeys =['currencyCode', 'currencySymbol'];
        $queryData = [
            'salesChannel' => $channelId,
        ];

        $this->client->request(
            'GET',
            $this->getUrl('marello_pricing_currency_by_channel') . '?' . http_build_query($queryData)
        );

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey(sprintf('currency-%s', $channelId), $responseData);
        foreach ($currencyDataKeys as $key) {
            $this->assertArrayHasKey($key, $responseData[sprintf('currency-%s', $channelId)]);
        }
    }
}
