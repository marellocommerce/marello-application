<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnData;

class ReturnControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadReturnData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_return_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testDatagrids()
    {
        $response = $this->client->requestGrid(
            'marello_report-returns-returned_qty_by_reason',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertNotCount(0, $result['data']);
        $this->assertLessThanOrEqual(100, (float) $result['data'][0]['percentageReturned']);
        $this->assertGreaterThanOrEqual(0, (float) $result['data'][0]['percentageReturned']);

        $response = $this->client->requestGrid(
            'marello_report-returns-returned_qty',
            []
        );

        $result = $this->getJsonResponseContent($response, 200);
        $this->assertNotCount(0, $result['data']);
        $this->assertNotEmpty($result['data'][0]['productSku']);
        $this->assertLessThanOrEqual(100, (float) $result['data'][0]['percentageReturned']);
        $this->assertGreaterThanOrEqual(0, (float) $result['data'][0]['percentageReturned']);
    }
}
