<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Marello\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadProductChannelPricingData;

/**
 * @dbIsolationPerTest
 * @nestTransactionsWithSavepoints
 */
class InventoryControllerTest extends WebTestCase
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadProductChannelPricingData::class,
            LoadInventoryData::class
        ]);

        $this->doctrine = $this->getContainer()->get('doctrine');
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
                    'id' => $this->getReference(LoadProductData::PRODUCT_1_REF)->getId()
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
                    'id' => $this->getReference(LoadProductData::PRODUCT_1_REF)->getId()
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
                        'pickLocation' => '11-11-11',
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

        $this->assertStringContainsString('Warehouse DE 1', $crawler->html());
        $this->assertStringContainsString('never_out_of_stock', $crawler->html());
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

        $this->assertStringContainsString('never_out_of_stock', $crawler->html());
    }
    /**
     * {@inheritdoc}
     */
    public function testUpdateBackorderAndPreorder()
    {
        /** @var InventoryItemManager $manager */
        $manager = $this->getContainer()->get('marello_inventory.manager.inventory_item_manager');
        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $manager->getInventoryItem($this->getReference(LoadProductData::PRODUCT_1_REF));

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

        $form   = $crawler->selectButton('Save and Close')->form();
        $formData = [
            'marello_inventory_item' => [
                'backorderAllowed' => true,
                'maxQtyToBackorder' => 10,
                'canPreorder' => true,
                'maxQtyToPreorder' => 10,
                'desiredInventory' => $inventoryItem->getDesiredInventory(),
                'purchaseInventory' => $inventoryItem->getPurchaseInventory(),
                'replenishment' => 'never_out_of_stock',
                '_token' => $form['marello_inventory_item[_token]']->getValue(),
            ],
        ];
        
        $this->client->followRedirects(true);
        $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var InventoryItem $savedInventoryItem */
        $savedInventoryItem = $this->doctrine
            ->getManagerForClass(InventoryItem::class)
            ->getRepository(InventoryItem::class)
            ->find($inventoryItem->getId());
        $this->assertEquals(true, $savedInventoryItem->isBackorderAllowed());
        $this->assertEquals(true, $savedInventoryItem->isCanPreorder());
        $this->assertEquals(10, $savedInventoryItem->getMaxQtyToBackorder());
        $this->assertEquals(10, $savedInventoryItem->getMaxQtyToPreorder());
    }
}
