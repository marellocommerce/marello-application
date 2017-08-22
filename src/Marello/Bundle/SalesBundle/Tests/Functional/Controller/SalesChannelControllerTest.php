<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;

class SalesChannelControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadSalesData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testCreateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_sales_saleschannel_update',
                ['id' => $this->getReference(LoadSalesData::CHANNEL_1_REF)]
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
