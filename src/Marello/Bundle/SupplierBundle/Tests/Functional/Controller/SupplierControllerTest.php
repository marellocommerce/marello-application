<?php

namespace Marello\Bundle\SupplierBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

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
            LoadProductData::class,
        ]);
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_supplier_supplier_index'));
        $result = $this->client->getResponse();
        $this->assertContains('supplier-grid', $crawler->html());
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testCreateNewSupplier()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_supplier_supplier_create'));
        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'Supplier 1';
        $form['marello_supplier_form[name]']               = $name;
        $form['marello_supplier_form[address][country]']     = 'NL';
        $form['marello_supplier_form[address][street]']      = 'Street 1';
        $form['marello_supplier_form[address][city]']        = 'Eindhoven';
        $form['marello_supplier_form[address][postalCode]']  = '5617BC';
        $form['marello_supplier_form[priority]']             = 0;
        $form['marello_supplier_form[canDropship]']          = true;
        $form['marello_supplier_form[isActive]']             = true;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Supplier saved', $crawler->html());
        $this->assertContains($name, $crawler->html());

        $response = $this->client->requestGrid(
            'marello-supplier-grid',
            ['marello-supplier-grid[_filter][name][value]' => $name]
        );

        $this->getJsonResponseContent($response, Response::HTTP_OK);

        return $name;
    }

    public function testSupplierView()
    {
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $response = $this->client->requestGrid(
            'marello-supplier-grid',
            ['marello-supplier-grid[_filter][name][value]' => $supplier->getName()]
        );
        $this->getJsonResponseContent($response, Response::HTTP_OK);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_view', ['id' => $supplier->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains($supplier->getName(), $crawler->html());
    }

    /**
     * @depends testSupplierView
     */
    public function testSupplierViewHasLinkedProducts()
    {
        /** @var Supplier $supplier */
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_view', ['id' => $supplier->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('supplier-products-grid', $crawler->html());
    }

    public function testLinkedProductToSupplier()
    {
        /** @var Supplier $supplier */
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $product  = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $response = $this->client->requestGrid(
            'marello-supplier-products-grid',
            [
                'marello-supplier-products-grid[supplierId]' => $supplier->getId(),
                'marello-supplier-products-grid[_filter][name][value]' => $product->getName()
            ]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $this->assertContains($product->getName(), $result['name']);
        $this->assertContains($product->getSku(), $result['sku']);
    }

    /**
     * @depends testCreateNewSupplier
     */
    public function testUpdateSupplier($name)
    {
        $response = $this->client->requestGrid(
            'marello-supplier-grid',
            ['marello-supplier-grid[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_update', ['id' => $result['id']])
        );
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        /** @var Form $form */
        $form                                           = $crawler->selectButton('Save and Close')->form();
        $name                                           = 'name' . $this->generateRandomString();
        $priority                                       = 10;
        $newStreet                                      = 'Street 2';
        $form['marello_supplier_form[name]']            = $name;
        $form['marello_supplier_form[priority]']        = $priority;
        $form['marello_supplier_form[address][street]'] = $newStreet;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("Supplier saved", $crawler->html());
        $this->assertContains($name, $crawler->html());
        $this->assertContains($newStreet, $crawler->html());
        $this->assertContains("{$priority}", $crawler->html());
    }

    public function testGetAddress()
    {
        /** @var MarelloAddress $address */
        $address = $this->getReference(LoadSupplierData::SUPPLIER_3_REF)->getAddress();
        $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_address', [
                'id'               => $address->getId(),
                'typeId'           => 1,
                '_widgetContainer' => 'block',
            ])
        );
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertContains($address->getStreet(), $response->getContent());
        $this->assertContains($address->getCountry()->getName(), $response->getContent());
    }

    public function testUpdateAddress()
    {
        $supplier   = $this->getReference(LoadSupplierData::SUPPLIER_3_REF);
        $address    = $supplier->getAddress();
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_supplier_supplier_updateaddress', [
                'id'               => $address->getId(),
                '_widgetContainer' => 'dialog',
            ])
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form           = $crawler->selectButton('Save')->form();
        $newStreet      = $this->generateRandomString();
        $countryCode    = 'US';
        $countryName    = 'United States';
        $postalCode     = '1234';

        $form['marello_address[street]']        = $newStreet;
        $form['marello_address[country]']       = $countryCode;
        $form['marello_address[postalCode]']    = $postalCode;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        $this->assertContains($newStreet, $crawler->html());
        $this->assertContains($countryName, $crawler->html());
        $this->assertContains($postalCode, $crawler->html());
    }

    public function testGetSupplierDefaultDataById()
    {
        /** @var Supplier $supplier */
        $supplier   = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);

        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_supplier_supplier_get_default_data',
                [
                    'supplier_id'    => $supplier->getId(),
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->getJsonResponseContent($response, Response::HTTP_OK);

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('priority', $responseData);
        $this->assertArrayHasKey('canDropship', $responseData);

        $this->assertEquals($responseData['priority'], $supplier->getPriority());
        $this->assertEquals($responseData['canDropship'], $supplier->getCanDropship());
    }
}
