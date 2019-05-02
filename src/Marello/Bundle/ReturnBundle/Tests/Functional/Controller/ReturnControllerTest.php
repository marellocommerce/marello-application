<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnData;

class ReturnControllerTest extends WebTestCase
{
    public function setUp()
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
}
