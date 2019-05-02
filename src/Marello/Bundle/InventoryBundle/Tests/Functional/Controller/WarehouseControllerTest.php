<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    public function testUpdateDefaultAvailable()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_inventory_warehouse_updatedefault')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $data = [
            'label'      => 'Warehouse 13',
            'country'    => 'NL',
            'street'     => 'Typicalstreetname',
            'street2'    => '30',
            'city'       => 'Westlife',
            'postalCode' => '2222',
            'phone'      => '90210'
        ];


        $form   = $crawler->selectButton('Save')->form();

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

        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        foreach ($data as $attribute => $value) {
            $this->assertContains($value, $crawler->html());
        }
    }
}
