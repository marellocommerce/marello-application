<?php

namespace Marello\Bundle\ReportBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    const ORDERS_REPORT_GROUP = 'orders';
    const ORDERS_GRID_NAME = 'marello_report-orders';
    const ORDERS_REPORT_NAME = 'revenue_per_sales_channel';
    const ORDERS_BESTSELLING_REPORT_NAME = 'best_selling_items';
    const ORDERS_WORSTSELLING_REPORT_NAME = 'worst_selling_items';

    const PRODUCTS_REPORT_GROUP = 'products';
    const PRODUCTS_GRID_NAME = 'marello_report-products';
    const PRODUCTS_REPORT_NAME = 'low_inventory_products';

    const RETURNS_REPORT_GROUP = 'returns';
    const RETURNS_GRID_NAME = 'marello_report-returns';
    const RETURN_QTY_BY_REASON_REPORT_NAME = 'returned_qty_by_reason';
    const RETURN_RETURNED_QTY_REPORT_NAME = 'returned_qty';

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    /**
     * Test Index of Marello report
     *
     * @param string $gridName
     * @param string $report
     * @param string $group
     * @param string $reportName
     * @dataProvider reportsProvider
     */
    public function testIndex($gridName, $report, $group, $reportName)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_report_index',
                [
                    'reportGroupName' => $group,
                    'reportName'      => $report,
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertStringContainsString($reportName, $result->getContent());
    }

    /**
     * Test Grids of marello report
     * @param string $gridName
     * @param string $report
     * @param string $group
     * @dataProvider reportsProvider
     */
    public function testGrid($gridName, $report, $group)
    {
        $reportName = $gridName . '-' . $report;
        $response = $this->client->requestGrid(
            $reportName,
            [
                "{$reportName}[reportGroupName]" => $group,
                "{$reportName}[reportName]"      => $report
            ]
        );

        $this->assertJsonResponseStatusCodeEquals($response, 200);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function reportsProvider()
    {
        return [
            'order_per_saleschannel'  => [
                self::ORDERS_GRID_NAME,
                self::ORDERS_REPORT_NAME,
                self::ORDERS_REPORT_GROUP,
                'Revenue per Sales Channel'
            ],
            'orders_best_selling_items'  => [
                self::ORDERS_GRID_NAME,
                self::ORDERS_BESTSELLING_REPORT_NAME,
                self::ORDERS_REPORT_GROUP,
                'Top selling products'
            ],
            'orders_worst_selling_items'  => [
                self::ORDERS_GRID_NAME,
                self::ORDERS_WORSTSELLING_REPORT_NAME,
                self::ORDERS_REPORT_GROUP,
                'Flop selling products'
            ],
            'low_inventory_products' => [
                self::PRODUCTS_GRID_NAME,
                self::PRODUCTS_REPORT_NAME,
                self::PRODUCTS_REPORT_GROUP,
                'Low Inventory Products'
            ],
            'returns_by_reason' => [
                self::RETURNS_GRID_NAME,
                self::RETURN_QTY_BY_REASON_REPORT_NAME,
                self::RETURNS_REPORT_GROUP,
                'Returned quantity by reason'
            ],
            'returns_returned_qty' => [
                self::RETURNS_GRID_NAME,
                self::RETURN_RETURNED_QTY_REPORT_NAME,
                self::RETURNS_REPORT_GROUP,
                'Returned quantity per product'
            ]
        ];
    }
}
