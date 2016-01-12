<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class ReturnControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadReturnData',
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_returns')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    public function testGet()
    {
        $testedReturn = $this->getReference('marello_return_1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_return_api_get_return', ['id' => $testedReturn->getId()])
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $result = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('returnNumber', $result);
        $this->assertArrayHasKey('returnItems', $result);

        $this->assertEquals($testedReturn->getId(), $result['id']);
        $this->assertEquals($testedReturn->getReturnNumber(), $result['returnNumber']);

        $this->assertCount($testedReturn->getReturnItems()->count(), $result['returnItems']);
    }

    /**
     */
    public function testCreate()
    {
        // TODO: test create
    }
}
