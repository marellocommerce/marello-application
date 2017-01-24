<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderDataTest;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );
        $this->loadFixtures([
            LoadOrderDataTest::class,
        ]);
    }

    public function testIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_orders')
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(10, json_decode($response->getContent(), true));
    }

    /**
     */
    public function testCreateWithCustomerId()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('marello_customer_1');
        $data = [
            'orderReference'  => 333444,
            'salesChannel'    => $this->getReference('marello_sales_channel_3')->getCode(),
            'subtotal'        => 365.00,
            'totalTax'        => 76.65,
            'grandTotal'      => 365.00,
            'paymentMethod'   => 'creditcard',
            'paymentDetails'  => 'Visa card, ref: xxxxxx-xxxx-xxxx',
            'shippingMethod'  => 'freeshipping',
            'discountAmount'  => 10,
            'couponCode'      => 'XFZDSFSDFSFSD',
            'shippingAmountInclTax'  => 8,
            'shippingAmountExclTax'  => 5,
            'customer'        => $customer->getId(),
            'billingAddress'  => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'shippingAddress' => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'items'           => [
                [
                    'product'           => 'msj005',
                    'quantity'          => 1,
                    'price'             => 150.10,
                    'originalPriceInclTax'     => 150.10,
                    'originalPriceExclTax'     => 140.10,
                    'purchasePriceIncl' => 190.00,
                    'tax'               => 39.90,
                    'taxPercent'        => 0.21,
                    'rowTotalInclTax'          => 190.00,
                    'rowTotalExclTax'          => 180.00,
                ],
                [
                    'product'           => 'msj003xs',
                    'quantity'          => 1,
                    'price'             => 138.25,
                    'originalPriceInclTax'     => 138.25,
                    'originalPriceExclTax'     => 128.25,
                    'purchasePriceIncl' => 175.00,
                    'tax'               => 36.75,
                    'taxPercent'        => 0.21,
                    'rowTotalInclTax'          => 175.00,
                    'rowTotalExclTax'          => 165.00,
                ],
            ],
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_order_api_post_order'),
            $data
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);

        /** @var Order $order */
        $order = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloOrderBundle:Order')
            ->findOneBy($response);

        $this->assertEquals($data['orderReference'], $order->getOrderReference());
        $this->assertCount(2, $order->getItems());
    }

    /**
     */
    public function testCreateWithCustomerData()
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference('marello_sales_channel_3');
        /** @var Product $product */
        $product = $this->getReference('marello-product-1');
        /** @var Product $product2 */
        $product2 = $this->getReference('marello-product-2');

        $data = [
            'orderReference'  => 333456,
            'salesChannel'    => $salesChannel->getCode(),
            'subtotal'        => 365.00,
            'totalTax'        => 76.65,
            'grandTotal'      => 365.00,
            'paymentMethod'   => 'creditcard',
            'paymentDetails'  => 'Visa card, ref: xxxxxx-xxxx-xxxx',
            'shippingMethod'  => 'freeshipping',
            'discountAmount'  => 10,
            'couponCode'      => 'XFZDSFSDFSFSD',
            'shippingAmountExclTax'  => 5,
            'shippingAmountInclTax'  => 7,
            'customer'        => [
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'email'     => 'new_customer_2@example.com',
                'primaryAddress'   => [
                    'firstName'  => 'John',
                    'lastName'   => 'Doe',
                    'country'    => 'NL',
                    'street'     => 'Torenallee 20',
                    'city'       => 'Eindhoven',
                    'region'     => 'NL-NB',
                    'postalCode' => '5617 BC',
                ],
            ],
            'billingAddress'  => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'shippingAddress' => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'items'          => [
                [
                    'product'           => $product->getSku(),
                    'productName'       => $product->getName(),
                    'quantity'          => 1,
                    'price'             => 150.10,
                    'originalPriceInclTax'     => 150.10,
                    'originalPriceExclTax'     => 140.10,
                    'purchasePriceIncl' => 190.00,
                    'tax'               => 39.90,
                    'taxPercent'        => 0.21,
                    'rowTotalInclTax'          => 190.00,
                    'rowTotalExclTax'          => 180.00,
                ],
                [
                    'product'           => $product2->getSku(),
                    'productName'       => $product2->getName(),
                    'quantity'          => 1,
                    'price'             => 138.25,
                    'originalPriceInclTax'     => 138.25,
                    'originalPriceExclTax'     => 128.25,
                    'purchasePriceIncl' => 175.00,
                    'tax'               => 36.75,
                    'taxPercent'        => 0.21,
                    'rowTotalInclTax'          => 175.00,
                    'rowTotalExclTax'          => 165.00,
                ],
            ],
        ];


        $this->client->request(
            'POST',
            $this->getUrl('marello_order_api_post_order'),
            $data
        );
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $response);

        /** @var Order $order */
        $order = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloOrderBundle:Order')
            ->findOneBy($response);

        $this->assertEquals($data['orderReference'], $order->getOrderReference());
        $this->assertCount(2, $order->getItems());
    }

    /**
     * @depends testCreateWithCustomerData
     */
    public function testGet()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => $this->getReference('marello_order_0')->getId()])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * @depends testCreateWithCustomerData
     */
    public function testUpdate()
    {
        $time = new \DateTime();
        $data = [
            'billingAddress'  => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'shippingAddress' => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Alderaan',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'paymentReference'  => 1223456,
            'invoicedAt'        => $time->format('d-m-Y H:i:s'),
            'invoiceReference'  => 666555444
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('marello_order_api_put_order', ['id' => $this->getReference('marello_order_0')->getId()]),
            $data
        );

        $response = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NO_CONTENT);
    }

    /**
     */
    public function testGetNotFound()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => 0])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
