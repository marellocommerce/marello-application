<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;

class InventoryLogLevelControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient(
            [],
            self::generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadInventoryData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testShowInventoryLogList()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventorylevel_index',
                ['id' => $product->getInventoryItem()->getId()]
            )
        );

        $response = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertStringContainsString('marello-inventory-log-extended', $crawler->html());

        $response = $this->client->requestGrid(
            'marello-inventory-log-extended',
            [
                'marello-inventory-log-extended[inventoryItemId]' => $product->getInventoryItem()->getId(),
            ]
        );
        $result = self::getJsonResponseContent($response, Response::HTTP_OK);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function testInventoryItemViewLogRecordGrid()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $response = $this->client->requestGrid('marello-inventory-log', [
            'marello-inventory-log[inventoryItemId]' => $product->getInventoryItem()->getId(),
        ]);
        $result = self::getJsonResponseContent($response, Response::HTTP_OK);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);

        $result = reset($result['data']);

        $this->assertEquals('Import', $result['changeTrigger']);
    }
}
