<?php

namespace Marello\Bundle\RefundBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadRefundData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class RefundControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([
            LoadRefundData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_refund_api_get_refunds')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    public function testGet()
    {
        $testedRefund = $this->getReference('marello_refund_1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_refund_api_get_refund', ['id' => $testedRefund->getId()])
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $result = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('refundNumber', $result);
        $this->assertArrayHasKey('refundAmount', $result);

        $this->assertEquals($testedRefund->getId(), $result['id']);
        $this->assertEquals($testedRefund->getRefundAmount(), $result['refundAmount']);

    }
}
