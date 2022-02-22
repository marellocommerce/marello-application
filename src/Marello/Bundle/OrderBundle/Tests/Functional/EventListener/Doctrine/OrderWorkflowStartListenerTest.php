<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\EventListener\Doctrine;

use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class OrderWorkflowStartListenerTest extends WebTestCase
{
    const TRANSIT_TO_STEP = 'pending';

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadOrderData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testOrderHasSingleWorkflowStartedByDefault()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->getContainer()->get('oro_workflow.manager');
        $workflowItems = $workflowManager->getWorkflowItemsByEntity($order);

        self::assertCount(1, $workflowItems);
    }

    /**
     * {@inheritdoc}
     */
    public function testWorkflowStartedIsDefaultWorkflow()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_0');
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->getContainer()->get('oro_workflow.manager');
        $workflowItems = $workflowManager->getWorkflowItemsByEntity($order);

        self::assertCount(1, $workflowItems);
        /** @var WorkflowItem $workflowItem */
        $workflowItem = array_shift($workflowItems);
        self::assertEquals(WorkflowNameProviderInterface::ORDER_WORKFLOW_1, $workflowItem->getWorkflowName());
    }

    /**
     * {@inheritdoc}
     */
    public function testWorkflowDidNotStartTwoActiveWorkflowsForOrderEntity()
    {
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->getContainer()->get('oro_workflow.manager');
        $workflowManager->activateWorkflow(WorkflowNameProviderInterface::ORDER_WORKFLOW_2);

        $order = $this->createOrder();

        /** @var WorkflowManager $workflowManager */
        $workflowItems = $workflowManager->getWorkflowItemsByEntity($order);

        self::assertCount(0, $workflowItems);
    }

    /**
     * {@inheritdoc}
     * @return Order
     */
    private function createOrder()
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
