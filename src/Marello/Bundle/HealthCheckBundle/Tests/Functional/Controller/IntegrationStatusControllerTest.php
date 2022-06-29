<?php

namespace Marello\Bundle\HealthCheckBundle\Tests\Functional\Controller;

use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\FilterBundle\Provider\DateModifierInterface;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadStatusData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IntegrationStatusControllerTest extends WebTestCase
{
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
            LoadStatusData::class,
        ]);
    }

    public function testIndexAction()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_healthcheck_integration_statuses_index',
                []
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testGridFilters()
    {
        // Render grid with default filter
        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['options']['totalRecords']);
        $this->assertStringContainsString('Failed', $result['data'][0]['code']);

        // Test code filter
        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            ['marello-integration-statuses-grid[_filter][code][value]' => Status::STATUS_FAILED]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('Failed', $result['data'][0]['code']);

        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            ['marello-integration-statuses-grid[_filter][code][value]' => Status::STATUS_COMPLETED]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(4, $result['data']);
        $this->assertStringContainsString('Completed', $result['data'][0]['code']);

        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            ['marello-integration-statuses-grid[_filter][code][value]' => null]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(5, $result['data']);

        // Test name filter
        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            [
                'marello-integration-statuses-grid[_filter][name][value]' => 'Foo Integration',
                'marello-integration-statuses-grid[_filter][code][value]' => null,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(4, $result['data']);
        $this->assertStringContainsString('Foo Integration', $result['data'][0]['name']);
        $this->assertStringContainsString('Foo Integration', $result['data'][1]['name']);

        // Test connector filter
        $response = $this->client->requestGrid(
            'marello-integration-statuses-grid',
            [
                'marello-integration-statuses-grid[_filter][connector][value]' => 'second_connector',
                'marello-integration-statuses-grid[_filter][code][value]' => null,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('second_connector', $result['data'][0]['connector']);
    }

    public function testWidgetGrid()
    {
        // Test that grid without default filters
        $response = $this->client->requestGrid(
            'marello-last-integration-statuses-grid',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(5, $result['data']);

        // Test code and dateRange filters
        $response = $this->client->requestGrid(
            'marello-last-integration-statuses-grid',
            [
                'marello-last-integration-statuses-grid[widgetConfiguration][code]' => Status::STATUS_FAILED,
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][start]' => null,
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][end]' => null,
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][part]'
                    => DateModifierInterface::PART_ALL_TIME,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('Failed', $result['data'][0]['code']);

        $response = $this->client->requestGrid(
            'marello-last-integration-statuses-grid',
            [
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][start]'
                    => (new \DateTime())->format('Y-m-d'),
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][end]' => null,
                'marello-last-integration-statuses-grid[widgetConfiguration][dateRange][part]'
                    => AbstractDateFilterType::TYPE_BETWEEN,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertCount(0, $result['data']);
    }
}
