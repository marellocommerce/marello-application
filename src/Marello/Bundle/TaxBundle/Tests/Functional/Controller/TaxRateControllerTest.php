<?php

namespace Marello\Bundle\TaxBundle\Tests\Functional\Controller;

use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaxRateControllerTest extends WebTestCase
{
    const TAX_CODE = 'unique';
    const TAX_CODE_UPDATED = 'uniqueUpdated';
    const TAX_RATE = 1;
    const TAX_RATE_UPDATED = 2;

    const TAX_SAVE_MESSAGE = 'Tax Rate saved';

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxrate_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('marello-taxrate-grid', $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_tax_taxrate_create'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxSave($crawler, self::TAX_CODE, self::TAX_RATE);

        /** @var TaxRate $taxRate */
        $taxRate = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloTaxBundle:TaxRate')
            ->getRepository('MarelloTaxBundle:TaxRate')
            ->findOneBy(['code' => self::TAX_CODE]);
        $this->assertNotEmpty($taxRate);

        return $taxRate->getId();
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
            $this->getUrl('marello_tax_taxrate_update', ['id' => $id])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertTaxSave($crawler, self::TAX_CODE_UPDATED, self::TAX_RATE_UPDATED);

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
            $this->getUrl('marello_tax_taxrate_view', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertViewPage($html, self::TAX_CODE_UPDATED, self::TAX_RATE_UPDATED);
    }

    /**
     * @param Crawler $crawler
     * @param string  $code
     * @param string  $rate
     */
    protected function assertTaxSave(Crawler $crawler, $code, $rate)
    {
        $form = $crawler->selectButton('Save and Close')->form(
            [
                'input_action' => '{"route":"marello_tax_taxrate_view","params":{"id":"$id"}}',
                'marello_tax_rate_form[code]' => $code,
                'marello_tax_rate_form[rate]' => $rate,
            ]
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains(self::TAX_SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $code, $rate);
    }

    /**
     * @param string $html
     * @param string $code
     * @param string $rate
     */
    protected function assertViewPage($html, $code, $rate)
    {
        $this->assertContains($code, $html);
        $this->assertContains($rate . '%', $html);
    }
}
