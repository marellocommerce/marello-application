<?php

namespace Marello\Bundle\ReportBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ReportControllerTest extends WebTestCase
{
    const ORDERS_REPORT_GROUP = 'orders';
    const ORDERS_REPORT_NAME = 'revenue_per_sales_channel';
    const ORDERS_GRID_NAME = 'marello_report-orders';

    const PRODUCTS_REPORT_GROUP = 'products';
    const PRODUCTS_REPORT_NAME = 'low_inventory_products';
    const PRODUCTS_GRID_NAME = 'marello_report-products';

    /**
     * {@inheritdoc}
     */
    public function setUp()
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
        $this->assertContains($reportName, $result->getContent());
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
            self::ORDERS_REPORT_NAME  => [
                self::ORDERS_GRID_NAME,
                self::ORDERS_REPORT_NAME,
                self::ORDERS_REPORT_GROUP,
                'Revenue per Sales Channel'
            ],
            self::PRODUCTS_REPORT_NAME => [
                self::PRODUCTS_GRID_NAME,
                self::PRODUCTS_REPORT_NAME,
                self::PRODUCTS_REPORT_GROUP,
                'Low Inventory Products'
            ],
        ];
    }
}
