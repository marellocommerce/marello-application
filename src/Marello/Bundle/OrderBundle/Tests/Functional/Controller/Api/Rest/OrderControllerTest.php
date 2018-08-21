<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class OrderControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );
        $this->loadFixtures([
            LoadOrderData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetOrderById()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => $this->getReference('marello_order_1')->getId()])
        );

        $response = $this->client->getResponse();
        $this->hasArrayKeysInResponse($response);
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetOrderList()
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
     * {@inheritdoc}
     */
    public function testCreateWithCustomerId()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer1');
        $data = [
            'orderReference'  => 333444,
            'salesChannel'    => $this->getReference(LoadSalesData::CHANNEL_1_REF)->getCode(),
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
                    'product'               => 'p1',
                    'quantity'              => 1,
                    'price'                 => 150.10,
                    'originalPriceInclTax'  => 150.10,
                    'originalPriceExclTax'  => 140.10,
                    'purchasePriceIncl'     => 190.00,
                    'tax'                   => 39.90,
                    'taxCode'               => 'TAX_HIGH',
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 190.00,
                    'rowTotalExclTax'       => 180.00,
                ],
                [
                    'product'               => 'p2',
                    'quantity'              => 1,
                    'price'                 => 138.25,
                    'originalPriceInclTax'  => 138.25,
                    'originalPriceExclTax'  => 128.25,
                    'purchasePriceIncl'     => 175.00,
                    'tax'                   => 36.75,
                    'taxCode'               => 'TAX_VERY_HIGH',
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 175.00,
                    'rowTotalExclTax'       => 165.00,
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
     * {@inheritdoc
     */
    public function testCreateWithCustomerData()
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getReference(LoadSalesData::CHANNEL_1_REF);
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);
        /** @var Product $product2 */
        $product2 = $this->getReference(LoadProductData::PRODUCT_2_REF);

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
                    'product'               => $product->getSku(),
                    'productName'           => $product->getName(),
                    'quantity'              => 1,
                    'price'                 => 150.10,
                    'originalPriceInclTax'  => 150.10,
                    'originalPriceExclTax'  => 140.10,
                    'purchasePriceIncl'     => 190.00,
                    'tax'                   => 39.90,
                    'taxCode'               => 'TAX_HIGH',
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 190.00,
                    'rowTotalExclTax'       => 180.00,
                ],
                [
                    'product'               => $product2->getSku(),
                    'productName'           => $product2->getName(),
                    'quantity'              => 1,
                    'price'                 => 138.25,
                    'originalPriceInclTax'  => 138.25,
                    'originalPriceExclTax'  => 128.25,
                    'purchasePriceIncl'     => 175.00,
                    'tax'                   => 36.75,
                    'taxCode'               => 'TAX_VERY_HIGH',
                    'taxPercent'            => 0.21,
                    'rowTotalInclTax'       => 175.00,
                    'rowTotalExclTax'       => 165.00,
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

        return $response;
    }

    /**
     * {@inheritdoc}
     * @depends testCreateWithCustomerData
     */
    public function testGetNewlyCreatedOrderById($response)
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => $response['id']])
        );

        $response = $this->client->getResponse();
        $this->hasArrayKeysInResponse($response);
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * @depends testCreateWithCustomerData
     */
    public function testUpdateOrderAddressAndInvoiceData($orderCreateResponse)
    {
        $time = new \DateTime();
        $newBillingAddress = [
            'billingAddress'  => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ]
        ];

        $newShippingAddress = [
            'shippingAddress' => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Alderaan',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ]
        ];
        $data = [
            'paymentReference'  => 1223456,
            'invoicedAt'        => $time->format('d-m-Y H:i:s'),
            'invoiceReference'  => 666555444
        ];
        $data = array_merge($newBillingAddress, $newShippingAddress, $data);
        $this->client->request(
            'PUT',
            $this->getUrl('marello_order_api_put_order', ['id' => $orderCreateResponse['id']]),
            $data
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NO_CONTENT);

        // check if order data is updated
        /** @var Order $order */
        $order = $this->client->getContainer()
            ->get('doctrine')
            ->getRepository('MarelloOrderBundle:Order')
            ->find($orderCreateResponse['id']);


        $this->assertEquals(333456, $order->getOrderReference());

        $this->assertEquals($data['paymentReference'], $order->getPaymentReference());
        $this->assertEquals($time->format('d-m-Y H:i:s'), $order->getInvoicedAt()->format('d-m-Y H:i:s'));
        $this->assertEquals($data['invoiceReference'], $order->getInvoiceReference());

        $this->assertNotEquals($order->getBillingAddress()->getCity(), $order->getShippingAddress()->getCity());
    }

    /**
     * Test order not found
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

    /**
     * Test if response has the correct fields for Order API repsonse
     * @param Response $response
     */
    protected function hasArrayKeysInResponse($response)
    {
        $jsonDecoded = json_decode($response->getContent(), true);
        foreach ($this->getFields() as $index => $fields) {
            foreach (array_keys($fields) as $field) {
                $this->assertArrayHasKey($field, $jsonDecoded);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getFields()
    {
        $addressConfig = [
            'fields'           => [
                'namePrefix'   => [],
                'firstName'    => [],
                'middleName'   => [],
                'lastName'     => [],
                'nameSuffix'   => [],
                'street'       => [],
                'street2'      => [],
                'city'         => [],
                'country'      => [],
                'region'       => [],
                'organization' => [],
                'postalCode'   => [],
                'phone'        => [],
            ],
        ];

        $itemConfig = [
            'fields'                => [
                'id'                => [],
                'productName'       => [],
                'productSku'        => [],
                'quantity'          => [],
                'price'             => [],
                'originalPriceExclTax'     => [],
                'originalPriceInclTax'     => [],
                'purchasePriceIncl' => [],
                'tax'               => [],
                'taxCode'           => [],
                'taxPercent'        => [],
                'rowTotalExclTax'          => [],
                'rowTotalInclTax'          => [],
            ],
        ];

        $config = [
            'fields'            => [
                'id'              => [],
                'orderNumber'     => [],
                'orderReference'  => [],
                'subtotal'        => [],
                'totalTax'        => [],
                'grandTotal'      => [],
                'paymentMethod'   => [],
                'paymentDetails'  => [],
                'shippingMethod'  => [],
                'shippingAmountInclTax'  => [],
                'shippingAmountExclTax'  => [],
                'salesChannel'    => [],
                'workflowItems'   => [],
                'items'           => $itemConfig,
                'billingAddress'  => $addressConfig,
                'shippingAddress' => $addressConfig,
            ],
        ];

        return $config;
    }
}
