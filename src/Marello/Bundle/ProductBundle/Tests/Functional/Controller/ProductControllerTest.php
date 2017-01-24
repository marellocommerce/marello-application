<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\Datafixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Tests\Functional\Datafixtures\LoadSalesData;
use Marello\Bundle\SupplierBundle\Tests\Functional\Datafixtures\LoadSupplierData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ProductControllerTest extends WebTestCase
{
    const GRID_NAME = 'marello-products-grid';

    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadSalesData::class,
            LoadProductData::class,
            LoadSupplierData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('marello_product_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    public function testCreateProduct()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_product_create'));
        $name    = 'Super duper product';
        $sku     = 'SKU-1234';
        $form    = $crawler->selectButton('Save and Close')->form();

        $form['marello_product_form[name]']               = $name;
        $form['marello_product_form[sku]']                = $sku;
        $form['marello_product_form[status]']             = 'enabled';
        $form['marello_product_form[desiredStockLevel]']  = 10;
        $form['marello_product_form[purchaseStockLevel]'] = 2;
        $form['marello_product_form[addSalesChannels]']   = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Product saved', $crawler->html());
        $this->assertContains($name, $crawler->html());

        return $name;
    }

    public function testUpdateProductSuppliers()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $crawler = $this->client->request('GET', $this->getUrl('marello_product_update', ['id' => $product->getId()]));

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();

        $this->assertTrue($product->hasSuppliers());

        $productSuppliers = [
            [
                'supplier' => $this->getReference(LoadSupplierData::SUPPLIER_1_REF)->getId(),
                'quantityOfUnit' => 24,
                'priority' => 2,
                'cost' => 33.55,
                'canDropship' => true
            ],
            [
                'supplier' => $this->getReference(LoadSupplierData::SUPPLIER_1_REF)->getId(),
                'quantityOfUnit' => 48,
                'priority' => 3,
                'cost' => 42.50,
                'canDropship' => true
            ],
            [
                'supplier' => $this->getReference(LoadSupplierData::SUPPLIER_2_REF)->getId(),
                'quantityOfUnit' => 100,
                'priority' => 1,
                'cost' => 60.99,
                'canDropship' => false
            ]
        ];

        $submittedData = [
            'input_action' => 'save_and_stay',
            'marello_product_form' => [
                '_token' => $form['marello_product_form[_token]']->getValue(),
                'suppliers' => $productSuppliers,
            ]
        ];

        $this->client->followRedirects(true);

        // Submit form
        $result = $this->client->getResponse();
        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /**
     * @param string $name
     *
     * @depends testCreateProduct
     *
     * @return string
     */
    public function testUpdateProduct($name)
    {
        $response = $this->client->requestGrid(
            'marello-products-grid',
            ['marello-products-grid[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $resultData = $result;
        $crawler     = $this->client->request(
            'GET',
            $this->getUrl('marello_product_update', ['id' => $result['id']])
        );
        $result      = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form                                              = $crawler->selectButton('Save and Close')->form();
        $name                                              = 'name' . $this->generateRandomString();
        $form['marello_product_form[name]']                = $name;
        $form['marello_product_form[desiredStockLevel]']   = 20;
        $form['marello_product_form[purchaseStockLevel]']  = 10;
        $form['marello_product_form[removeSalesChannels]'] = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $form['marello_product_form[addSalesChannels]']    = $this->getReference(LoadSalesData::CHANNEL_2_REF)->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("Product saved", $crawler->html());

        $resultData['name'] = $name;

        return $resultData;
    }

    /**
     * @param array $resultData
     *
     * @depends testUpdateProduct
     *
     * @return string
     */
    public function testProductView($resultData)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_product_view', ['id' => $resultData['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("{$this->getReference(LoadSalesData::CHANNEL_2_REF)->getName()}", $crawler->html());
        $this->assertContains("{$resultData['name']}", $crawler->html());
    }

    /**
     * @param array $resultData
     *
     * @depends testUpdateProduct
     *
     * @return string
     */
    public function testProductInfo($resultData)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'marello_product_widget_info',
                ['id' => $resultData['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains($resultData['name'], $crawler->html());
    }
}
