<?php

namespace Marello\Bundle\RefundBundle\Tests\Functional\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\RefundBundle\Tests\Functional\DataFixtures\LoadRefundData;

class RefundControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadRefundData::class,
            LoadOrderData::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_refund_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function testUpdateRefund()
    {
        /** @var Refund $refund */
        $refund = $this->getReference('marello_refund_0');
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_refund_update', ['id' => $refund->getId()])
        );

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form    = $crawler->selectButton('Save and Close')->form();
        $key = array_key_first ($form->get('marello_refund[items]'));
        $form[sprintf('marello_refund[items][%d][quantity]', $key)]  = 1;
        $form[sprintf('marello_refund[items][%d][refundAmount]', $key)] = 100;

        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $this->assertStringContainsString('Refund saved', $crawler->html());
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateRefundFromOrder()
    {
        $response = $this->client->requestGrid('marello-refund');
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $initialResult = count($result['data']);

        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_refund_create', ['id' => $order->getId()])
        );
        $form    = $crawler->selectButton('Save and Close')->form();
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $this->assertStringContainsString('Refund saved', $crawler->html());

        $response = $this->client->requestGrid('marello-refund');
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $newRefundResultCount = count($result['data']);
        $this->assertSame(($initialResult+1), $newRefundResultCount);
    }
}
