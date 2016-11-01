<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller;

use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderDataTest;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadOrderDataTest::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testCreateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_view', ['id' => $this->getReference('marello_order_0')->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_update', ['id' => $this->getReference('marello_order_0')->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testGetAddress()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_address', [
                'id'               => $this->getReference('marello_order_0')->getBillingAddress()->getId(),
                'typeId'           => 1,
                '_widgetContainer' => 'block',
            ])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateAddress()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_updateaddress', [
                'id'               => $this->getReference('marello_order_0')->getBillingAddress()->getId(),
                '_widgetContainer' => 'dialog',
            ])
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save')->form();
        $name = 'Han Solo';
        $lastName = 'Solo';

        $form['marello_address[firstName]'] = $name;
        $form['marello_address[lastName]'] = $lastName;

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }
}
