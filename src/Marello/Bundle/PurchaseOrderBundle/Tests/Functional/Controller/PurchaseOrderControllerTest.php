<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Controller;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadPurchaseOrderData;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderDataTest;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class PurchaseOrderControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadPurchaseOrderDataTest::class]);
    }

    /** @test */
    public function testIndexAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_index'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /** @test */
    public function testViewAction()
    {
        /** @var PurchaseOrder $po */
        $po = $this->getReference('marello-purchase-order-1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_purchaseorder_purchaseorder_view', ['id' => $po->getId()])
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /** @test */
    public function testSelectProductsAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_selectproducts'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /** @test */
    public function testCreateAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
