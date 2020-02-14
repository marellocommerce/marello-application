<?php

namespace Marello\Bundle\SubscriptionBundle\Tests\Functional\Controller;

use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionAttributeFamilyData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
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
            array_merge(self::generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
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

        $xPath = "//select[@name='marello_product_step_one[type]']/option[contains(text(),'Simple')]";
        $this->assertEquals(
            1,
            $crawler->filterXPath($xPath)->count()
        );

        $xPath = "//select[@name='marello_product_step_one[type]']/option[contains(text(),'Subscription')]";
        $this->assertEquals(
            1,
            $crawler->filterXPath($xPath)->count()
        );
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var AttributeFamily $attributeFamily */
        $attributeFamily = $em
            ->getRepository(AttributeFamily::class)
            ->findOneBy(['code' => LoadSubscriptionAttributeFamilyData::SUBSCRIPTION_FAMILY_CODE]);
        $formStepOne = $crawler->selectButton('Continue')->form();
        $formValues = $formStepOne->getPhpValues();
        $formValues['input_action'] = 'marello_product_create';
        $formValues['marello_product_step_one']['type'] = 'subscription';
        $formValues['marello_product_step_one']['attributeFamily'] = $attributeFamily->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_product_create'),
            $formValues
        );

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertContains('Subscription', $crawler->html());

        $name    = 'Super duper product';
        $sku     = 'SKU-1234';
        $form    = $crawler->selectButton('Save and Close')->form();

        $form['marello_product_form[names][values][default]'] = $name;
        $form['marello_product_form[sku]'] = $sku;
        $form['marello_product_form[status]'] = 'enabled';
        $form['marello_product_form[addSalesChannels]'] =
            $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $form['marello_product_form[taxCode]'] =
            $this->getReference(LoadTaxCodeData::TAXCODE_3_REF)->getId();
        $form['marello_product_form[subscriptionDuration]'] = '12';
        $form['marello_product_form[number_of_deliveries]'] = 1;
        $form['marello_product_form[paymentTerm]'] = '01';
        $form['marello_product_form[specialPriceDuration]'] = 'equal_to_subscription_duration';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        self::assertContains('Product saved', $crawler->html());
        self::assertContains($name, $crawler->html());
        self::assertContains('Subscription Duration', $crawler->html());
        self::assertContains('12 months', $crawler->html());
        self::assertContains('Payment Term', $crawler->html());
        self::assertContains('1 month', $crawler->html());
        self::assertContains('Number of Deliveries', $crawler->html());
        self::assertContains('Special Price Duration', $crawler->html());
        self::assertContains('equal to subscription duration', $crawler->html());

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
        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_3_REF)->getId();
        /** @var Form $form */
        $form                                              = $crawler->selectButton('Save and Close')->form();
        $name                                              = 'name' . $this->generateRandomString();
        $form['marello_product_form[names][values][default]'] = $name;
        $form['marello_product_form[removeSalesChannels]']
            = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
        $form['marello_product_form[addSalesChannels]']
            = $this->getReference(LoadSalesData::CHANNEL_2_REF)->getId();
        $form['marello_product_form[taxCode]']             = $taxCode;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        self::assertContains("Product saved", $crawler->html());

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
        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        self::assertContains(
            (string) $this->getReference(LoadSalesData::CHANNEL_2_REF)->getName(),
            $crawler->html()
        );
        self::assertContains((string) $resultData['name'], $crawler->html());
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
        self::assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        self::assertContains($resultData['name'], $crawler->html());
    }
}
