<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

/**
 * @dbIsolation
 */
class InventoryControllerTest extends WebTestCase
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

    /**
     * {@inheritdoc}
     */
    public function testViewAction()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventory_view',
                [
                    'id' => $this->getReference(LoadProductData::PRODUCT_1_REF)
                ]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function testUpdateActionAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventory_update',
                [
                    'id' => $this->getReference(LoadProductData::PRODUCT_1_REF)
                ]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
