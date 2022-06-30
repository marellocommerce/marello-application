<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Tests\Functional\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\PackingBundle\Entity\PackingSlip;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerDropshipmentTest extends WebTestCase
{
    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var Registry
     */
    protected $doctrine;
    
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadOrderData::class,
        ]);

        $this->workflowManager = $this->getContainer()->get('oro_workflow.manager');
        $this->doctrine = $this->getContainer()->get('doctrine');
    }

    /**
     * @return Order
     */
    public function testCreateWithOwnWarehouse()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_order_order_create'));
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        /** @var Customer $orderCustomer */
        $orderCustomer = $this->getReference('marello-customer-1');

        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);

        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $price = $product->getSalesChannelPrice($salesChannel)->getPrice()->getValue();
        $orderItems = [
            [
                'product' => $product->getId(),
                'quantity' => 1,
                'availableInventory' => 1,
                'price' => $price,
                'tax' => 0.00,
                'taxCode' => $product->getTaxCode()->getCode(),
                'rowTotalExclTax' => $price,
                'rowTotalInclTax' => $price
            ],
        ];
        $submittedData = $this->getSubmittedData($form, $orderCustomer, $salesChannel, $orderItems);

        $this->client->followRedirects(true);

        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Order $order */
        $order = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloOrderBundle:Order')
            ->getRepository('MarelloOrderBundle:Order')
            ->findOneBy([
                'customer' => $orderCustomer->getId(),
                'salesChannel' => $salesChannel->getId(),
                'subtotal' => $price
            ]);
        $this->assertNotEmpty($order);

        $orderItem = $order->getItems()[0];
        static::assertSame($product->getSku(), $orderItem->getProductSku());

        return $order;
    }
    
    /**
     * @depends testCreateWithOwnWarehouse
     * @param Order $entity
     */
    public function testAssigningOwnWarehouse(Order $entity)
    {
        $workflowItem = $this->workflowManager
            ->getWorkflowItem($entity, 'marello_order_b2c_workflow_1');
        if (!$workflowItem) {
            $workflowItem = $this->workflowManager
                ->startWorkflow('marello_order_b2c_workflow_1', $entity, 'pending');
        }
        $this->workflowManager->transit($workflowItem, 'invoice');
        $data = $workflowItem->getData();
        $data
            ->set('payment_reference', 'payment_reference')
            ->set('payment_details', 'payment_details')
            ->set('total_paid', 100);
        $workflowItem->setData($data);
        $this->workflowManager->transit($workflowItem, 'payment_received');
        $this->workflowManager->transit($workflowItem, 'prepare_shipping');

        /** @var Allocation $allocation */
        $allocation = $this->doctrine
            ->getManagerForClass(Allocation::class)
            ->getRepository(Allocation::class)
            ->findOneBy([
                'order' => $entity
            ]);

        $this->assertNotEmpty($allocation);
    }

    /**
     * @return Order
     */
    public function testCreateWithOwnAndExternalWarehouse()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_order_order_create'));
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        /** @var Customer $orderCustomer */
        $orderCustomer = $this->getReference('marello-customer-1');

        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);

        /** @var Product $ownProduct */
        $ownProduct = $this->getReference(LoadProductData::PRODUCT_1_REF);
        /** @var Product $externalProduct */
        $externalProduct = $this->getReference(LoadProductData::PRODUCT_5_REF);

        $price1 = $ownProduct->getPrice($salesChannel)->getPrice()->getValue();
        $price2 = $externalProduct->getPrice($salesChannel)->getPrice()->getValue();
        $orderItems = [
            [
                'product' => $ownProduct->getId(),
                'quantity' => 1,
                'availableInventory' => 1,
                'price' => $price1,
                'tax' => 0.00,
                'taxCode' => $ownProduct->getTaxCode()->getCode(),
                'rowTotalExclTax' => $price1,
                'rowTotalInclTax' => $price1
            ],
            [
                'product' => $externalProduct->getId(),
                'quantity' => 1,
                'availableInventory' => 1,
                'price' => $price2,
                'tax' => 0.00,
                'taxCode' => $externalProduct->getTaxCode()->getCode(),
                'rowTotalExclTax' => $price2,
                'rowTotalInclTax' => $price2
            ],
        ];
        $submittedData = $this->getSubmittedData($form, $orderCustomer, $salesChannel, $orderItems);

        $this->client->followRedirects(true);

        $this->client->request($form->getMethod(), $form->getUri(), $submittedData);
        $result  = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Order $order */
        $order = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloOrderBundle:Order')
            ->getRepository('MarelloOrderBundle:Order')
            ->findOneBy([
                'customer' => $orderCustomer->getId(),
                'salesChannel' => $salesChannel->getId(),
                'subtotal' => (float)$price1 + (float)$price2
            ]);
        $this->assertNotEmpty($order);

        $actualItems = $order->getItems();
        $orderItem1 = $actualItems[0];
        static::assertSame($ownProduct->getSku(), $orderItem1->getProductSku());
        $orderItem2 = $actualItems[1];
        static::assertSame($externalProduct->getSku(), $orderItem2->getProductSku());

        return $order;
    }

    /**
     * @depends testCreateWithOwnAndExternalWarehouse
     * @param Order $entity
     */
    public function testAssigningOwnAndExternalWarehouse(Order $entity)
    {
        $workflowItem = $this->workflowManager
            ->getWorkflowItem($entity, 'marello_order_b2c_workflow_1');
        if (!$workflowItem) {
            $workflowItem = $this->workflowManager
                ->startWorkflow('marello_order_b2c_workflow_1', $entity, 'pending');
        }
        $this->workflowManager->transit($workflowItem, 'invoice');
        $data = $workflowItem->getData();
        $data
            ->set('payment_reference', 'payment_reference')
            ->set('payment_details', 'payment_details')
            ->set('total_paid', 100);
        $workflowItem->setData($data);
        $this->workflowManager->transit($workflowItem, 'payment_received');
        $this->workflowManager->transit($workflowItem, 'prepare_shipping');

//        /** @var PackingSlip[] $packingSlips */
//        $packingSlips = $this->doctrine
//            ->getManagerForClass(PackingSlip::class)
//            ->getRepository(PackingSlip::class)
//            ->findBy([
//                'order' => $entity
//            ]);
//        $this->assertCount(2, $packingSlips);

        /** @var Product $ownProduct */
        $ownProduct = $this->getReference(LoadProductData::PRODUCT_1_REF);
        /** @var Product $externalProduct */
        $externalProduct = $this->getReference(LoadProductData::PRODUCT_5_REF);

        /** @var Allocation $allocation */
        $allocations = $this->doctrine
            ->getManagerForClass(Allocation::class)
            ->getRepository(Allocation::class)
            ->findBy([
                'order' => $entity
            ]);
        $this->assertCount(2, $allocations);

        foreach ($allocations as $allocation) {
            $allocationItems = $allocation->getItems()->toArray();
            $this->assertCount(1, $allocationItems);
            /** @var AllocationItem $allocationItem */
            $allocationItem = reset($allocationItems);
            $orderItem = $allocationItem->getOrderItem();
            $warehouseType = $allocation->getWarehouse()->getWarehouseType()->getName();
            if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                $this->assertEquals($allocationItem->getProductSku(), $externalProduct->getSku());
                $this->assertEquals(LoadOrderItemStatusData::PENDING, $orderItem->getStatus()->getId());
            } else {
                $this->assertEquals($allocationItem->getProductSku(), $ownProduct->getSku());
                $this->assertEquals(LoadOrderItemStatusData::PROCESSING, $orderItem->getStatus()->getId());
            }
        }
    }

    /**
     * @param Form $form
     * @param Customer $orderCustomer
     * @param SalesChannel $salesChannel
     * @param $orderItems
     * @return array
     */
    private function getSubmittedData($form, $orderCustomer, $salesChannel, $orderItems)
    {
        $paymentMethodProvider = $this->getContainer()->get('marello_payment.payment_method.composite_provider');
        $paymentMethods = $paymentMethodProvider->getPaymentMethods();
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = reset($paymentMethods);
        /** @var ShippingMethodProviderInterface $shippingMethodsProvider */
        $shippingMethodsProvider = $this->getContainer()->get('marello_shipping.shipping_method_provider');
        $shippingMethods = $shippingMethodsProvider->getShippingMethods();
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = reset($shippingMethods);
        $shippingMethodTypes = $shippingMethod->getTypes();
        /** @var ShippingMethodTypeInterface $shippingMethodType */
        $shippingMethodType = reset($shippingMethodTypes);
        $submittedData = [
            'input_action' => 'save_and_stay',
            'marello_order_order' => [
                '_token' => $form['marello_order_order[_token]']->getValue(),
                'customer' => $orderCustomer->getId(),
                'salesChannel' => $salesChannel->getId(),
                'items' => $orderItems,
                'billingAddress' => $this->getAddressFormData($orderCustomer->getPrimaryAddress()),
                'shippingAddress' => $this->getAddressFormData($orderCustomer->getPrimaryAddress()),
                'calculateShipping' => true,
                'paymentMethod' => $paymentMethod->getIdentifier(),
                'shippingMethod' => $shippingMethod->getIdentifier(),
                'shippingMethodType' => $shippingMethodType->getIdentifier(),
                'estimatedShippingCostAmount' => 5.00
            ]
        ];

        return $submittedData;
    }

    /**
     * @param MarelloAddress $address
     * @return array
     */
    private function getAddressFormData(MarelloAddress $address)
    {
        return [
            'namePrefix' => $address->getNamePrefix(),
            'firstName' => $address->getFirstName(),
            'middleName' => $address->getMiddleName(),
            'lastName' => $address->getLastName(),
            'nameSuffix' => $address->getNameSuffix(),
            'country' => $address->getCountryIso2(),
            'street' => $address->getStreet(),
            'street2' => $address->getStreet2(),
            'city' => $address->getCity(),
            'region' => $address->getRegionCode(),
            'region_text' => $address->getRegionText(),
            'postalCode' => $address->getPostalCode(),
            'phone' =>$address->getPhone(),
            'company' => $address->getCompany()
        ];
    }
}
