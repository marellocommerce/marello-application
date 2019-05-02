<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Logging;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Logging\ChartBuilder;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures\LoadInventoryData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ChartBuilderTest extends WebTestCase
{
    /**
     * @var ChartBuilder
     */
    protected $chartBuilder;

    /**
     * @var InventoryItemManager
     */
    protected $itemManager;

    /**
     * @var InventoryTotalCalculator
     */
    protected $totalCalculator;

    public function setUp()
    {
        $this->initClient();

        $this->chartBuilder = $this->client->getContainer()->get('marello_inventory.logging.chart_builder');
        $this->itemManager = $this->client->getContainer()->get('marello_inventory.manager.inventory_item_manager');
        $this->totalCalculator = $this->client
            ->getContainer()
            ->get('marello_inventory.model.inventory_level_totals_calculator');

        $this->loadFixtures([
            LoadProductData::class,
            LoadInventoryData::class
        ]);
    }

    /**
     * @test
     */
    public function testGetChartData()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        /** @var InventoryItem $inventoryItem */
        $inventoryItem = $this->itemManager->getInventoryItem($product);

        /*
         * Get start and end points of interval +- 3 days around creation of this single log.
         */
        $from = clone $product->getCreatedAt();
        $to   = clone $product->getCreatedAt();

        $from->modify('- 3 days');
        $to->modify('+ 3 days');

        $data = $this->chartBuilder->getChartData(
            $inventoryItem,
            $from,
            $to
        );

        $this->assertCount(3, $data, 'Data should contain values for one warehouse. (based on demo data)');

        /*
         * Get single warehouse from result.
         */
        $data = reset($data);

        $this->assertCount(7, $data, 'For given test interval, there should be 6 generated values.');

        $first = reset($data);
        $last  = end($data);

        $this->assertEquals(0, $first['inventory'], 'First item stock level should be zero.');
        $this->assertEquals(
            $this->totalCalculator->getTotalInventoryQty($inventoryItem),
            $last['inventory'],
            'Last item stock level should be same as the one stored in inventory item.'
        );
    }
}
