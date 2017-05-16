<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class WarehouseControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
    }

    /**
     * @test
     * {@inheritdoc}
     */
    public function isWarehouseGridRenderedWithDefaultWarehouse()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_index')
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertContains('Warehouse', $response->getContent());
        $this->assertNotEmpty('Create Warehouse', $crawler->html());
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateNewWarehouse()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_create')
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $data = [
            'label'      => 'Warehouse 13',
            'country'    => 'NL',
            'street'     => 'Typicalstreetname',
            'street2'    => '30',
            'city'       => 'Westlife',
            'postalCode' => '2222',
            'phone'      => '90210'
        ];


        $form   = $crawler->selectButton('Save and Close')->form();

        $form['marello_warehouse[label]']               = $data['label'];
        $form['marello_warehouse[address][country]']    = $data['country'];
        $form['marello_warehouse[address][street]']     = $data['street'];
        $form['marello_warehouse[address][street2]']    = $data['street2'];
        $form['marello_warehouse[address][city]']       = $data['city'];
        $form['marello_warehouse[address][postalCode]'] = $data['postalCode'];
        $form['marello_warehouse[address][phone]']      = $data['phone'];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Warehouse saved', $crawler->html());
        $this->assertContains($data['label'], $crawler->html());

        return $data['label'];
    }

    /**
     * @depends testCreateNewWarehouse
     */
    public function testUpdateExistingWarehouse($label)
    {
        $response = $this->client->requestGrid(
            'marello-enterprise-inventory-warehouse-grid',
            ['marello-enterprise-inventory-warehouse-grid[_column][label][value]' => $label]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $updateLink = $result['update_link'];
        $id = str_replace('/marello/inventory/warehouse/update/', '', $updateLink);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marelloenterprise_inventory_warehouse_update', ['id' => $id])
        );
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        /** @var Form $form */
        $form                                           = $crawler->selectButton('Save and Close')->form();
        $name                                           = 'name' . $this->generateRandomString();
        $form['marello_warehouse[label]']                = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains($name, $crawler->html());
    }
}
