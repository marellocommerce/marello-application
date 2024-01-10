<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
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

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * @return int
     */
    public function testCreate()
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
        static::assertSame($order->getOrganization(), $orderItem->getOrganization());
        static::assertEquals(
            $product->getInventoryItem()->getProductUnit(),
            $orderItem->getProduct()->getInventoryItem()->getProductUnit()
        );

        static::assertEquals(9.0, $order->getSubtotal());
        static::assertEquals(5.0, $order->getShippingCostAmount());
        static::assertEquals(14.0, $order->getGrandTotal());

        return $order->getId();
    }
    
    /**
     * @depends testCreate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertStringContainsString('marello-order-packingslips', $crawler->html());
    }

    /**
     * @depends testCreate
     * @param int $id
     */
    public function testUpdateAvailable($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testGetAddress()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_address', [
                'id'               => $this->getReference('marello_order_0')->getBillingAddress()->getId(),
                'typeId'           => 1,
                '_widgetContainer' => 'block',
            ])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testUpdateAddress()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_order_order_updateaddress', [
                'id'               => $this->getReference('marello_order_0')->getBillingAddress()->getId(),
                '_widgetContainer' => 'dialog',
            ])
        );

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);

        $form = $crawler->selectButton('Save')->form();
        $name = 'Han Solo';
        $lastName = 'Solo';

        $form['marello_address[firstName]'] = $name;
        $form['marello_address[lastName]'] = $lastName;

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($result, Response::HTTP_OK);
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
