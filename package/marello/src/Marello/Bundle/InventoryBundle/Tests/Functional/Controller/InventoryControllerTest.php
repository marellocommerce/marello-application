<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

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
            LoadInventoryData::class
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

    /**
     * {@inheritdoc}
     */
    public function testUpdateInventoryItemAddLevelAndIncrease()
    {
        /** @var InventoryItemManager $manager */
        $manager = $this->getContainer()->get('marello_inventory.manager.inventory_item_manager');
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $manager->getInventoryItem($this->getReference(LoadProductData::PRODUCT_1_REF));
        $this->assertEquals(true, $inventoryItem->hasInventoryLevels());

        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken('marello_inventory_inventory_update')->getValue();

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventory_update',
                [
                    'id' => $inventoryItem->getId()
                ]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $formData = [
            'marello_inventory_item' => [
                'inventoryLevels' => [
                    [
                        'warehouse' => 1,
                        'adjustmentOperator' => InventoryLevelCalculator::OPERATOR_INCREASE,
                        'quantity' => 10
                    ]
                ],
                'desiredInventory' => $inventoryItem->getDesiredInventory(),
                'purchaseInventory' => $inventoryItem->getPurchaseInventory(),
                'replenishment' => 'never_out_of_stock',
                '_token' => $token,
            ],
        ];

        $form   = $crawler->selectButton('Save and Close')->form();
        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Warehouse DE 1', $crawler->html());
        $this->assertContains('never_out_of_stock', $crawler->html());
    }

    /**
     * {@inheritdoc}
     */
    public function testUpdateInventoryItemRemoveLevels()
    {
        /** @var InventoryItemManager $manager */
        $manager = $this->getContainer()->get('marello_inventory.manager.inventory_item_manager');
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $manager->getInventoryItem($this->getReference(LoadProductData::PRODUCT_1_REF));
        $this->assertEquals(true, $inventoryItem->hasInventoryLevels());

        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken('marello_inventory_inventory_update')->getValue();

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'marello_inventory_inventory_update',
                [
                    'id' => $inventoryItem->getId()
                ]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $formData = [
            'marello_inventory_item' => [
                'inventoryLevels' => [],
                'desiredInventory' => $inventoryItem->getDesiredInventory(),
                'purchaseInventory' => $inventoryItem->getPurchaseInventory(),
                'replenishment' => $inventoryItem->getReplenishment(),
                '_token' => $token,
            ],
        ];

        $form   = $crawler->selectButton('Save and Close')->form();
        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('never_out_of_stock', $crawler->html());
    }
}
