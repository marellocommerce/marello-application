<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\Controller;

use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * @outputBuffering enabled
 */
class SubscriptionProductControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateProduct()
    {

        $crawler = $this->client->request('GET', $this->getUrl('marello_product_create'));
        $this->assertEquals(
            1,
            $crawler->filterXPath("//select[@name='marello_product_step_one[type]']/option[contains(text(),'Simple')]")->count()
        );

        $this->assertEquals(
            1,
            $crawler->filterXPath("//select[@name='marello_product_step_one[type]']/option[contains(text(),'Subscription')]")->count()
        );

        $formStepOne = $crawler->selectButton('Continue')->form();
        $formValues = $formStepOne->getPhpValues();
        $formValues['input_action'] = 'marello_product_create';
        $formValues['marello_product_step_one']['type'] = 'subscription';

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_product_create'),
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Subscription', $crawler->html());

        $name    = 'Super duper product';
        $sku     = 'SKU-1234';
        $form    = $crawler->selectButton('Save and Close')->form();

        $form['marello_product_form[name]'] = $name;
        $form['marello_product_form[sku]'] = $sku;
        $form['marello_product_form[status]'] = 'enabled';
        $form['marello_product_form[addSalesChannels]'] = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $form['marello_product_form[taxCode]'] = $this->getReference(LoadTaxCodeData::TAXCODE_3_REF)->getId();
        $form['marello_product_form[subscriptionDuration]'] = '12';
        $form['marello_product_form[number_of_deliveries]'] = 1;
        $form['marello_product_form[paymentTerm]'] = '01';
        $form['marello_product_form[specialPriceDuration]'] = 'equal_to_subscription_duration';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Product saved', $crawler->html());
        $this->assertContains($name, $crawler->html());
        $this->assertContains('Subscription Duration', $crawler->html());
        $this->assertContains('12 months', $crawler->html());
        $this->assertContains('Payment Term', $crawler->html());
        $this->assertContains('1 month', $crawler->html());
        $this->assertContains('Number of Deliveries', $crawler->html());
        $this->assertContains('Special Price Duration', $crawler->html());
        $this->assertContains('equal to subscription duration', $crawler->html());

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

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $resultData = $result;
        $crawler     = $this->client->request(
            'GET',
            $this->getUrl('marello_product_update', ['id' => $result['id']])
        );
        $result      = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_3_REF)->getId();
        /** @var Form $form */
        $form                                              = $crawler->selectButton('Save and Close')->form();
        $name                                              = 'name' . $this->generateRandomString();
        $form['marello_product_form[name]']                = $name;
        $form['marello_product_form[removeSalesChannels]'] = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $form['marello_product_form[addSalesChannels]']    = $this->getReference(LoadSalesData::CHANNEL_2_REF)->getId();
        $form['marello_product_form[taxCode]']             = $taxCode;

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
     * @depends testUpdateProduct
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
     * @depends testUpdateProduct
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
