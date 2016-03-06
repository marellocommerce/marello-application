<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

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
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadSalesData::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('marello_product_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreateProduct()
    {
        $crawler      = $this->client->request('GET', $this->getUrl('marello_product_create'));
        $name         = 'Super duper product';
        $sku          = 'SKU-1234';
        $form         = $crawler->selectButton('Save and Close')->form();


        $form['marello_product_form[name]']  = $name;
        $form['marello_product_form[sku]']   = $sku;
        $form['marello_product_form[addSalesChannels]'] = $this->getReference('marello_sales_channel_1')->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Product saved', $crawler->html());
        $this->assertContains($name, $crawler->html());

        return $name;
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

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $returnValue = $result;
        $crawler     = $this->client->request(
            'GET',
            $this->getUrl('marello_product_update', ['id' => $result['id']])
        );
        $result      = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form                               = $crawler->selectButton('Save and Close')->form();
        $name                               = 'name' . $this->generateRandomString();
        $form['marello_product_form[name]'] = $name;
        $form['marello_product_form[removeSalesChannels]'] = $this->getReference('marello_sales_channel_1')->getId();
        $form['marello_product_form[addSalesChannels]'] = $this->getReference('marello_sales_channel_2')->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Product saved", $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     *
     * @depends testUpdateProduct
     *
     * @return string
     */
    public function testProductView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_product_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("{$this->getReference('marello_sales_channel_2')->getName()}", $crawler->html());
        $this->assertContains("{$returnValue['name']}", $crawler->html());
    }

    /**
     * @param array $returnValue
     *
     * @depends testUpdateProduct
     *
     * @return string
     */
    public function testProductInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'marello_product_widget_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($returnValue['name'], $crawler->html());
    }
}
