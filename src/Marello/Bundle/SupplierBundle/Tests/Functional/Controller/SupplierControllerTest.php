<?php

namespace Marello\Bundle\SupplierBundle\Tests\Functional\Controller;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSupplierData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class SupplierControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadSupplierData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testCreateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_view', ['id' => $this->getReference('marello_supplier_0')->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateAvailable()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_update', ['id' => $this->getReference('marello_supplier_0')->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testGetAddress()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_address', [
                'id'               => $this->getReference('marello_supplier_0')->getAddress()->getId(),
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
            $this->getUrl('marello_supplier_supplier_updateaddress', [
                'id'               => $this->getReference('marello_supplier_0')->getAddress()->getId(),
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
