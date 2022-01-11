<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Functional\Controller;

use Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures\LoadInvoiceData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @outputBuffering enabled
 */
class InvoiceDownloadPdfControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadInvoiceData::class
        ]);
    }

    /**
     * @param $urlParams
     * @param $expectedDisposition
     *
     * @dataProvider getInvoiceActionProvider
     */
    public function testInvoice($urlParams, $expectedDisposition)
    {
        $invoice = $this->getReference('marello_invoice_0');

        $urlParams = array_merge($urlParams, ['entity' => 'invoice', 'id' => $invoice->getId()]);

        $this->client->request('GET', $this->getUrl('marello_pdf_download', $urlParams));
        $response = $this->client->getResponse();

        $contentDisposition = $response->headers->get('Content-Disposition');
        list($disposition, $filePart) = explode(';', $contentDisposition);
        $filePart = trim($filePart);

        $filenameRegexp = '/^filename=(["\']?.+["\']?)$/';
        $expectedFilename = sprintf('invoice_%s.pdf', $invoice->getInvoiceNumber());

        $matches = [];
        $result = preg_match($filenameRegexp, $filePart, $matches);
        $this->assertIsInt($result);
        $filename = trim($matches[1], '"\'');

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals($expectedDisposition, $disposition);
        $this->assertEquals($expectedFilename, $filename);
    }

    /**
     * @return array
     */
    public function getInvoiceActionProvider()
    {
        return [
            'inline implicit' => [
                'urlParams' => [],
                'expectedDisposition' => ResponseHeaderBag::DISPOSITION_INLINE,
            ],
            'inline explicit' => [
                'urlParams' => ['download' => false],
                'expectedDisposition' => ResponseHeaderBag::DISPOSITION_INLINE,
            ],
            'attachment' => [
                'urlParams' => ['download' => true],
                'expectedDisposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            ],
        ];
    }
}
