<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Controller;

use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class TaxCodeControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadTaxCodeData::class,
        ]);
    }

    /** @test */
    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxcode_index'));
        $result = $this->client->getResponse();
        $this->assertContains('marello-taxcode-grid', $crawler->html());
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /** @test */
    public function testCreateNewTaxCode()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxcode_create'));
        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $code = 'CODE 1';
        $description = 'Description 1';
        $form['input_action'] = '{"route":"marello_tax_taxcode_view","params":{"id":"$id"}}';
        $form['marello_tax_code_form[code]'] = $code;
        $form['marello_tax_code_form[description]'] = $description;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Tax Code saved', $crawler->html());
        $this->assertContains($code, $crawler->html());

        $response = $this->client->requestGrid(
            'marello-taxcode-grid',
            ['marello-taxcode-grid[_filter][code][value]' => $code]
        );

        $this->getJsonResponseContent($response, Response::HTTP_OK);

        return $code;
    }

    /** @test */
    public function testTaxCodeView()
    {
        $taxCode = $this->getReference(LoadTaxCodeData::TAXCODE_1_REF);
        $response = $this->client->requestGrid(
            'marello-taxcode-grid',
            ['marello-taxcode-grid[_filter][code][value]' => $taxCode->getCode()]
        );
        $this->getJsonResponseContent($response, Response::HTTP_OK);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_tax_taxcode_view', ['id' => $taxCode->getId()])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains($taxCode->getCode(), $crawler->html());
    }

    /**
     * @depends testCreateNewTaxCode
     * @param string $code
     */
    public function testUpdateTaxCode($code)
    {
        $response = $this->client->requestGrid(
            'marello-taxcode-grid',
            ['marello-taxcode-grid[_filter][code][value]' => $code]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_tax_taxcode_update', ['id' => $result['id']])
        );
        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        /** @var Form $form */
        $form                                           = $crawler->selectButton('Save and Close')->form();
        $code                                           = 'code'. $this->generateRandomString();
        $description                                    = 'description'. $this->generateRandomString();
        $form['marello_tax_code_form[code]']            = $code;
        $form['marello_tax_code_form[description]']     = $description;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("Tax Code saved", $crawler->html());
        $this->assertContains($code, $crawler->html());
        $this->assertContains($description, $crawler->html());
    }
}
