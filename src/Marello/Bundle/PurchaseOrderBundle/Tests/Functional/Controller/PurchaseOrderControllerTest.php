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
    public function testUpdateAction()
    {
        /** @var PurchaseOrder $po */
        $po = $this->getReference('marello-purchase-order-1');

        $this->client->request(
            'GET',
            $this->getUrl('marello_purchaseorder_purchaseorder_update', ['id' => $po->getId()])
        );

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /** @test */
    public function testCreateAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /** @test */
    public function testCreateStepTwoAction()
    {
        $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create_step_two'));

        $this->assertHtmlResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FOUND);
    }

//    /** @test */
//    public function testCreateDataAction()
//    {
//        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));
//
//        $name    = 'Super duper product';
//        $sku     = 'SKU-1234';
//        $form    = $crawler->selectButton('Save and Close')->form();
//
//        $form['marello_product_form[name]']               = $name;
//        $form['marello_product_form[sku]']                = $sku;
//        $form['marello_product_form[status]']             = 'enabled';
//        $form['marello_product_form[desiredStockLevel]']  = 10;
//        $form['marello_product_form[purchaseStockLevel]'] = 2;
//        $form['marello_product_form[addSalesChannels]']   = $this->getReference(LoadSalesData::CHANNEL_1_REF)->getId();
//        $form['marello_product_form[replenishment]']      = LoadProductReplenishmentData::NOS;
//
//        $this->client->followRedirects(true);
//        $crawler = $this->client->submit($form);
//        $result  = $this->client->getResponse();
//
//        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
//        $this->assertContains('Product saved', $crawler->html());
//        $this->assertContains($name, $crawler->html());
//
//        return $name;
//    }
}
