<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class InventoryLogControllerTest extends WebTestCase
{

    /**
     * @test
     */
    public function showsInventoryLogList()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData',
        ]);

        /*
         * Product used to retrieve inventory log.
         */
        $product = $this->getReference('marello-product-1');

        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventorylog_list',
                ['id' => $product->getId()]
            )
        );

        $response = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function testInventoryLogGrid()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData',
        ]);

        /*
         * Product used to retrieve inventory log.
         */
        $product = $this->getReference('marello-product-1');

        $this->client->requestGrid('marello-inventory-log', [
            'marello-inventory-log[productId]' => $product->getId(),
        ]);

        $response = $this->client->getResponse();

        $result = $this->getJsonResponseContent($response, 200);

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);

        $result = reset($result['data']);

        $this->assertEquals('Import', $result['actionType']);
    }
}
