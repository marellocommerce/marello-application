<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Logging;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Logging\ChartBuilder;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

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
        $product = $this->getReference('marello-product-1');

        /** @var InventoryItem $log */
        $inventoryItem = $product
            ->getInventoryItems()
            ->first();

        /** @var InventoryLog $log */
        $log = $inventoryItem
            ->getInventoryLogs()
            ->first();

        /*
         * Get start and end points of interval +- 3 days around creation of this single log.
         */
        $from = clone $log->getCreatedAt();
        $to   = clone $log->getCreatedAt();

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

        $this->assertEquals(0, $first['quantity'], 'First item quantity level should be zero.');
        $this->assertEquals(
            $inventoryItem->getQuantity(),
            $last['quantity'],
            'Last item quantity level should be same as the one stored in inventory item.'
        );
    }
}
