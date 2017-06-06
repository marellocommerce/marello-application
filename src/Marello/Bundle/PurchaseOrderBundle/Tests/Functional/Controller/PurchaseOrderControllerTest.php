<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Controller;

use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderItemType;
use Marello\Bundle\PurchaseOrderBundle\Form\Type\PurchaseOrderType;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;
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
        $this->loadFixtures([LoadPurchaseOrderData::class]);
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
        $po = $this->getReference(LoadPurchaseOrderData::PURCHASE_ORDER_1_REF);

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
        $po = $this->getReference(LoadPurchaseOrderData::PURCHASE_ORDER_2_REF);

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

    /** @test */
    public function testCreateAllStepsAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $form    = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'marello_purchaseorder_purchaseorder_create';
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $formValues['marello_purchase_order_create_step_one']['supplier'] = $supplier->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_purchaseorder_purchaseorder_create'),
            $formValues);

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product1->getId(),
            'orderedAmount' => 4
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 5
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added'] = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains('Purchase Order saved', $html);
    }

    /** @test */
    public function testErrorSupplierCreateAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $form    = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'marello_purchaseorder_purchaseorder_create';
        $formValues['marello_purchase_order_create_step_one']['supplier'] = null;

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_purchaseorder_purchaseorder_create'),
            $formValues);

        $result = $this->client->getResponse();
        $this->assertContains("This value should not be blank", $crawler->html());
    }


    /** @test */
    public function testErrorItemsCreateAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $form    = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'marello_purchaseorder_purchaseorder_create';
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $formValues['marello_purchase_order_create_step_one']['supplier'] = $supplier->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_purchaseorder_purchaseorder_create'),
            $formValues);

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains(PurchaseOrderType::VALIDATION_MESSAGE, $html);
    }


    /** @test */
    public function testErrorProductCreateAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $form    = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'marello_purchaseorder_purchaseorder_create';
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $formValues['marello_purchase_order_create_step_one']['supplier'] = $supplier->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_purchaseorder_purchaseorder_create'),
            $formValues);

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => null,
            'orderedAmount' => 4
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 5
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added'] = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains(PurchaseOrderItemType::VALIDATION_MESSAGE_PRODUCT, $html);
    }

    /** @test */
    public function testErrorOrderedAmountCreateAction()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_purchaseorder_purchaseorder_create'));

        $form    = $crawler->selectButton('Continue')->form();
        $formValues = $form->getPhpValues();
        $formValues['input_action'] = 'marello_purchaseorder_purchaseorder_create';
        $supplier = $this->getReference(LoadSupplierData::SUPPLIER_1_REF);
        $formValues['marello_purchase_order_create_step_one']['supplier'] = $supplier->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request(
            'POST',
            $this->getUrl('marello_purchaseorder_purchaseorder_create'),
            $formValues);

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product1->getId(),
            'orderedAmount' => 4
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 0
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added'] = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains(PurchaseOrderItemType::VALIDATION_MESSAGE_ORDERED_AMOUNT, $html);
    }
}
