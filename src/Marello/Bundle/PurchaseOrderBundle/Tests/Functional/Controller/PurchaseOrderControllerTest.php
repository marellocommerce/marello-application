<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;
use Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures\LoadPurchaseOrderData;

class PurchaseOrderControllerTest extends WebTestCase
{
    /** @var Warehouse $defaultWarehouse */
    protected $defaultWarehouse;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadPurchaseOrderData::class]);
        $this->defaultWarehouse = $this->getContainer()
            ->get('marello_inventory.repository.warehouse')
            ->getDefault();
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
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $tomorrow = new \DateTime('tomorrow');
        $formValues['marello_purchase_order_create_step_two']['dueDate'] = $tomorrow->format('Y-m-d');
        $formValues['marello_purchase_order_create_step_two']['warehouse'] = $this->defaultWarehouse->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product1->getId(),
            'orderedAmount' => 4,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 5,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added']
            = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $html = $crawler->html();

        $this->assertContains('Purchase Order saved succesfully', $html);
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
            $formValues
        );

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
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $tomorrow = new \DateTime('tomorrow');
        $formValues['marello_purchase_order_create_step_two']['dueDate'] = $tomorrow->format('Y-m-d');
        $formValues['marello_purchase_order_create_step_two']['items'] = array();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains('At least one item should be added', $html);
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
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();

        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $tomorrow = new \DateTime('tomorrow');
        $formValues['marello_purchase_order_create_step_two']['dueDate'] = $tomorrow->format('Y-m-d');
        $formValues['marello_purchase_order_create_step_two']['warehouse'] = $this->defaultWarehouse->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => null,
            'orderedAmount' => 4,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 5,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added']
            = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains('Product can not be null', $html);
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
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $tomorrow = new \DateTime('tomorrow');
        $formValues['marello_purchase_order_create_step_two']['dueDate'] = $tomorrow->format('Y-m-d');
        $formValues['marello_purchase_order_create_step_two']['warehouse'] = $this->defaultWarehouse->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product1->getId(),
            'orderedAmount' => 4,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 0,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added']
            = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains('Ordered Amount must be higher than 0', $html);
    }

    /** @test */
    public function testErrorDueDateCreateAction()
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
            $formValues
        );

        $result = $this->client->getResponse();
        $this->assertContains($supplier->getName(), $crawler->html());

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save and Close')->form();
        $formValues = $form->getPhpValues();
        $formValues['marello_purchase_order_create_step_two']['supplier'] = $supplier->getId();
        $tomorrow = new \DateTime('yesterday');
        $formValues['marello_purchase_order_create_step_two']['dueDate'] = $tomorrow->format('Y-m-d');
        $formValues['marello_purchase_order_create_step_two']['warehouse'] = $this->defaultWarehouse->getId();
        $formValues['marello_purchase_order_create_step_two']['items'] = array();
        $product1 = $this->getReference(LoadProductData::PRODUCT_1_REF);
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product1->getId(),
            'orderedAmount' => 4,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['items'][] = array(
            'product' => $product2->getId(),
            'orderedAmount' => 5,
            'purchasePrice' => [
                'currency' => 'USD',
                'value'     => 10
            ]
        );
        $formValues['marello_purchase_order_create_step_two']['itemsAdvice']['added']
            = ''. $product1->getid() . ','. $product2->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formValues);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $html = $crawler->html();
        $this->assertContains('Due date must be greater than', $html);
    }

    /**
     * @test
     * @depends testCreateAllStepsAction
     */
    public function testFilterBySupplierOnGrid()
    {
        /** @var PurchaseOrder $po */
        $po = $this->getReference(LoadPurchaseOrderData::PURCHASE_ORDER_1_REF);
        $supplier = $po->getSupplier();
        $response = $this->client->requestGrid(
            'marello-purchase-order',
            ['marello-purchase-order[_filter][supplier][value]' => $supplier->getName()]
        );

        self::assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertContains($supplier->getName(), $response->getContent());
    }
}
