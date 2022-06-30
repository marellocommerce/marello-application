<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

class TaxJurisdictionControllerTest extends WebTestCase
{
    const CODE = 'code';
    const DESCRIPTION = 'description';
    const COUNTRY = 'ZW';
    const COUNTRY_FULL = 'Zimbabwe';
    const STATE = 'ZW-MA';
    const STATE_FULL = 'Manicaland';

    const CODE_UPDATED = 'codeUpdated';
    const DESCRIPTION_UPDATED = 'description updated';
    const COUNTRY_UPDATED = 'HN';
    const COUNTRY_FULL_UPDATED = 'Honduras';
    const STATE_UPDATED = 'HN-CH';
    const STATE_FULL_UPDATED = 'Choluteca';
    
    /**
     * @var array
     */
    protected static $zipCodes = [
        ['zipRangeStart' => '11111', 'zipRangeEnd' => null],
        ['zipRangeStart' => '00001', 'zipRangeEnd' => '000003'],
        ['zipRangeStart' => null, 'zipRangeEnd' => '22222'],
    ];

    /**
     * @var array
     */
    protected static $zipCodesUpdated = [
        ['zipRangeStart' => '11111', 'zipRangeEnd' => null],
        ['zipRangeStart' => '00001', 'zipRangeEnd' => '000005'],
        ['zipRangeStart' => null, 'zipRangeEnd' => '22222'],
    ];

    const SAVE_MESSAGE = 'Tax Jurisdiction saved';

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxjurisdiction_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertStringContainsString('marello-taxjurisdiction-grid', $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxjurisdiction_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxJurisdictionSave(
            $crawler,
            self::CODE,
            self::DESCRIPTION,
            self::COUNTRY,
            self::COUNTRY_FULL,
            self::STATE,
            self::STATE_FULL,
            self::$zipCodes
        );

        /** @var TaxJurisdiction $taxJurisdiction */
        $taxJurisdiction = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloTaxBundle:TaxJurisdiction')
            ->getRepository('MarelloTaxBundle:TaxJurisdiction')
            ->findOneBy(['code' => self::CODE]);
        $this->assertNotEmpty($taxJurisdiction);

        return $taxJurisdiction->getId();
    }

    /**
     * @param int $id
     * @return int
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_tax_taxjurisdiction_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxJurisdictionSave(
            $crawler,
            self::CODE_UPDATED,
            self::DESCRIPTION_UPDATED,
            self::COUNTRY_UPDATED,
            self::COUNTRY_FULL_UPDATED,
            self::STATE_UPDATED,
            self::STATE_FULL_UPDATED,
            self::$zipCodesUpdated
        );

        return $id;
    }

    /**
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_tax_taxjurisdiction_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertViewPage(
            $html,
            self::CODE_UPDATED,
            self::DESCRIPTION_UPDATED,
            self::COUNTRY_FULL_UPDATED,
            self::STATE_FULL_UPDATED
        );
    }

    /**
     * @param Crawler $crawler
     * @param string  $code
     * @param string  $description
     * @param string  $country
     * @param string  $countryFull
     * @param string  $state
     * @param string  $stateFull
     * @param array   $zipCodes
     */
    protected function assertTaxJurisdictionSave(
        Crawler $crawler,
        $code,
        $description,
        $country,
        $countryFull,
        $state,
        $stateFull,
        array $zipCodes
    ) {
        $form = $crawler->selectButton('Save and Close')->form();
        $formData = $form->getPhpValues();
        $formData['input_action'] = '{"route":"marello_tax_taxjurisdiction_view","params":{"id":"$id"}}';
        $formData['marello_tax_jurisdiction_type']['code'] = $code;
        $formData['marello_tax_jurisdiction_type']['description'] = $description;
        $formData['marello_tax_jurisdiction_type']['zipCodes'] = $zipCodes;

        $formData = $this->setCountryAndState($form, $formData, $country, $countryFull, $state, $stateFull);

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertStringContainsString(self::SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $code, $description, $countryFull, $stateFull);
    }

    /**
     * @param Form $form
     * @param array $formData
     * @param string $country
     * @param string $countryFull
     * @param string $state
     * @param string $stateFull
     * @return array
     */
    protected function setCountryAndState(Form $form, array $formData, $country, $countryFull, $state, $stateFull)
    {
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select name="marello_tax_jurisdiction_type[country]" ' .
            'id="oro_tax_jurisdiction_type_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $country . '">' . $countryFull . '</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $formData['marello_tax_jurisdiction_type']['country'] = $country;

        $doc->loadHTML(
            '<select name="marello_tax_jurisdiction_type[region]" ' .
            'id="oro_tax_jurisdiction_type_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $state . '">' . $stateFull . '</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $formData['marello_tax_jurisdiction_type']['region'] = $state;

        return $formData;
    }

    /**
     * @param string $html
     * @param string $code
     * @param string $description
     * @param string $country
     * @param string $state
     */
    protected function assertViewPage($html, $code, $description, $country, $state)
    {
        $this->assertStringContainsString($code, $html);
        $this->assertStringContainsString($description, $html);
        $this->assertStringContainsString($country, $html);
        $this->assertStringContainsString($state, $html);
    }
}
