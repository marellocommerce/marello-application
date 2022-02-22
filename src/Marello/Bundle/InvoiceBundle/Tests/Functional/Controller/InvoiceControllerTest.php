<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Functional\Controller;

use Marello\Bundle\InvoiceBundle\Entity\Creditmemo;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures\LoadInvoiceData;
use Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures\LoadCreditmemoData;

class InvoiceControllerTest extends WebTestCase
{
    const GRID_NAME = 'marello-invoices-base-grid';

    public function setUp(): void
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadInvoiceData::class,
            LoadCreditmemoData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('marello_invoice_invoice_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $response = $this->client->requestGrid(self::GRID_NAME);
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        self::assertCount(6, $result['data']);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexFilter()
    {
        $datagridNameAndFilter = sprintf('%s[_filter][invoiceType][value]', self::GRID_NAME);

        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [
                $datagridNameAndFilter => Invoice::INVOICE_TYPE
            ]
        );
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        self::assertCount(4, $result['data']);

        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [
                $datagridNameAndFilter => Creditmemo::CREDITMEMO_TYPE
            ]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        self::assertCount(2, $result['data']);
    }
}
