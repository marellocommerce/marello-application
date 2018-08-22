<?php

namespace Marello\Bundle\RefundBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\RefundBundle\Tests\Functional\DataFixtures\LoadRefundData;

class RefundControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadRefundData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_refund_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
