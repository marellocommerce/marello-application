<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\InvoiceBundle\Tests\Functional\DataFixtures\LoadInvoiceData;
use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\OrderBundle\Entity\Order;

class InvoiceJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloinvoices';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadInvoiceData::class
        ]);
    }

    /**
     * Test cget (getting a list of invoices) of Invoice entity
     *
     */
    public function testGetListOfInvoices()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(4, $response);
        $this->assertResponseContains('cget_invoice_list.yml', $response);
    }

    /**
     * Test get invoice by id
     */
    public function testGetInvoiceById()
    {
        /** @var Invoice $invoice */
        $invoice = $this->getReference('marello_invoice_1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $invoice->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseContains('get_invoice_by_id.yml', $response);
    }

    /**
     * Test get invoice by invoiceNumber
     */
    public function testGetInvoiceByInvoiceNumber()
    {
        /** @var Invoice $invoice */
        $invoice = $this->getReference('marello_invoice_1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $invoice->getId()],
            [
                'filter' => ['invoiceNumber' =>  $invoice->getInvoiceNumber() ]
            ]
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseContains('get_invoice_by_invoiceNumber.yml', $response);
    }

    /**
     * Test cget (getting a list of invoices) of Invoice entity filter by order id
     *
     */
    public function testGetListOfInvoicesFilteredByOrder()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_2');

        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['order' =>  $order->getId() ]
            ]
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(1, $response);
        $this->assertResponseContains('cget_invoice_list_by_order.yml', $response);
    }
}
