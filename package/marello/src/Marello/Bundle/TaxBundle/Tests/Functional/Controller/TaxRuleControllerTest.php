<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData;
use Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRateData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaxRulesControllerTest extends WebTestCase
{
    const TAX_RULE_SAVE_MESSAGE = 'Tax Rule saved';

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxCodeData',
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxRateData',
                'Marello\Bundle\TaxBundle\Tests\Functional\DataFixtures\LoadTaxJurisdictionData',
            ]
        );
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxrule_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('marello-taxrule-grid', $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxrule_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxRuleSave(
            $crawler,
            $this->getTaxCode(LoadTaxCodeData::TAXCODE_0_REF),
            $this->getTaxRate(LoadTaxRateData::CODE_1),
            $this->getTaxJurisdiction(LoadTaxRateData::CODE_1)
        );

        /** @var TaxRule $taxRule */
        $taxRule = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloTaxBundle:TaxRule')
            ->getRepository('MarelloTaxBundle:TaxRule')
            ->findOneBy(['taxCode' => $this->getTaxCode(LoadTaxCodeData::TAXCODE_0_REF)]);
        $this->assertNotEmpty($taxRule);

        return $taxRule->getId();
    }

    /**
     * @param string $reference
     * @return TaxCode
     */
    protected function getTaxCode($reference)
    {
        return $this->getReference($reference);
    }

    /**
     * @param string $reference
     * @return TaxRate
     */
    protected function getTaxRate($reference)
    {
        return $this->getReference(LoadTaxRateData::REFERENCE_PREFIX . '.' . $reference);
    }

    /**
     * @param string $reference
     * @return TaxJurisdiction
     */
    protected function getTaxJurisdiction($reference)
    {
        return $this->getReference(LoadTaxJurisdictionData::REFERENCE_PREFIX . '.' . $reference);
    }

    /**
     * @param $id int
     * @return int
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_tax_taxrule_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxRuleSave(
            $crawler,
            $this->getTaxCode(LoadTaxCodeData::TAXCODE_1_REF),
            $this->getTaxRate(LoadTaxRateData::CODE_2),
            $this->getTaxJurisdiction(LoadTaxRateData::CODE_2)
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
            $this->getUrl('marello_tax_taxrule_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertViewPage(
            $html,
            $this->getTaxCode(LoadTaxCodeData::TAXCODE_1_REF),
            $this->getTaxRate(LoadTaxRateData::CODE_2),
            $this->getTaxJurisdiction(LoadTaxRateData::CODE_2)
        );
    }

    /**
     * @param Crawler         $crawler
     * @param TaxCode         $taxCode
     * @param TaxRate         $taxRate
     * @param TaxJurisdiction $taxJurisdiction
     */
    protected function assertTaxRuleSave(
        Crawler $crawler,
        TaxCode $taxCode,
        TaxRate $taxRate,
        TaxJurisdiction $taxJurisdiction
    ) {
        $form = $crawler->selectButton('Save and Close')->form(
            [
                'input_action' => '{"route":"marello_tax_taxrule_view","params":{"id":"$id"}}',
                'marello_tax_rule_form[taxCode]' => $taxCode->getId(),
                'marello_tax_rule_form[taxRate]' => $taxRate->getId(),
                'marello_tax_rule_form[taxJurisdiction]' => $taxJurisdiction->getId(),
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains(self::TAX_RULE_SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $taxCode, $taxRate, $taxJurisdiction);
    }

    /**
     * @param string          $html
     * @param TaxCode         $taxCode
     * @param TaxRate         $taxRate
     * @param TaxJurisdiction $taxJurisdiction
     */
    protected function assertViewPage(
        $html,
        TaxCode $taxCode,
        TaxRate $taxRate,
        TaxJurisdiction $taxJurisdiction
    ) {
        $this->assertContains($taxCode->getCode(), $html);
        $this->assertContains($taxRate->getCode(), $html);
        $this->assertContains($taxJurisdiction->getCode(), $html);
    }
}
