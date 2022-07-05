<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Entity;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class InventoryItemTest extends WebTestCase
{
    /**
     * @var InventoryManager
     */
    protected $manager;

    /**
     * @var InventoryItemManager
     */
    protected $itemManager;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient();

        $this->manager = $this->client->getContainer()->get('marello_inventory.manager.inventory_manager');
        $this->itemManager = $this->client->getContainer()->get('marello_inventory.manager.inventory_item_manager');
    }

    public function testCreateAndUpdateNewInventoryItem()
    {
        $organization = new Organization();
        /** @var Product $product */
        $product = new Product();
        $product->setOrganization($organization);

        $inventoryItem = $this->itemManager->createInventoryItem($product);

        $this->assertEquals(false, $inventoryItem->hasInventoryLevels());
        $this->assertEmpty($inventoryItem->getInventoryLevels());
        $this->assertEmpty($inventoryItem->getReplenishment());
        $this->assertEmpty($inventoryItem->getProductUnit());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $product,
            $inventoryItem,
            10,
            20,
            'import'
        );

        $this->manager->updateInventoryLevel($context);

        $this->assertEquals(true, $inventoryItem->hasInventoryLevels());
        $this->assertNotEmpty($inventoryItem->getInventoryLevels());
    }

    public function testIfInventoryItemIsUpdatedCorrectlyWithMultipleChanges()
    {
        $organization = new Organization();
        /** @var Product $product */
        $product = new Product();
        $product->setOrganization($organization);

        $inventoryItem = $this->itemManager->createInventoryItem($product);

        $this->assertEquals(false, $inventoryItem->hasInventoryLevels());
        $this->assertEmpty($inventoryItem->getInventoryLevels());
        $this->assertEmpty($inventoryItem->getReplenishment());
        $this->assertEmpty($inventoryItem->getProductUnit());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $product,
            $inventoryItem,
            10,
            20,
            'manual'
        );

        $this->manager->updateInventoryLevel($context);

        $this->assertEquals(true, $inventoryItem->hasInventoryLevels());
        $this->assertNotEmpty($inventoryItem->getInventoryLevels());

        /** @var InventoryLevel $inventoryLevel */
        $inventoryLevel = $inventoryItem->getInventoryLevels()->first();
        $this->assertEquals(10, $inventoryLevel->getInventoryQty());
        $this->assertEquals(20, $inventoryLevel->getAllocatedInventoryQty());
        $this->assertEquals((10 - 20), $inventoryLevel->getVirtualInventoryQty());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryLevelUpdateContext(
            $inventoryLevel,
            $inventoryItem,
            [],
            -5,
            -10,
            'manual'
        );

        $this->manager->updateInventoryLevel($context);

        /** @var InventoryLevel $inventoryLevel */
        $inventoryLevel = $inventoryItem->getInventoryLevels()->first();
        $this->assertEquals(5, $inventoryLevel->getInventoryQty());
        $this->assertEquals(10, $inventoryLevel->getAllocatedInventoryQty());
        $this->assertEquals((5 - 10), $inventoryLevel->getVirtualInventoryQty());
    }
}
