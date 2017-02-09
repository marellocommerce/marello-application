<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Logging;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Marello\Bundle\InventoryBundle\Logging\ChartBuilder;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

/**
 * @dbIsolation
 */
class ChartBuilderTest extends WebTestCase
{
    /** @var ChartBuilder */
    protected $chartBuilder;

    public function setUp()
    {
        $this->initClient();

        $this->chartBuilder = $this->client->getContainer()->get('marello_inventory.logging.chart_builder');

        $this->loadFixtures([
            LoadProductData::class
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
        $inventoryItem = $product
            ->getInventoryItems()
            ->first();

        /** @var StockLevel $inventory */
        $inventory = $inventoryItem->getCurrentLevel();

        /*
         * Get start and end points of interval +- 3 days around creation of this single log.
         */
        $from = clone $inventory->getCreatedAt();
        $to   = clone $inventory->getCreatedAt();

        $from->modify('- 3 days');
        $to->modify('+ 3 days');

        $data = $this->chartBuilder->getChartData(
            $product,
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
            $inventoryItem->getStock(),
            $last['inventory'],
            'Last item stock level should be same as the one stored in inventory item.'
        );
    }
}
