<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
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
            LoadOrderData::class,
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
    public function testCreate()
    {
        $data = [
            'orderReference'  => 333444,
            'salesChannel'    => $this->getReference('marello_sales_channel_3')->getId(),
            'subtotal'        => 365.00,
            'totalTax'        => 76.65,
            'grandTotal'      => 365.00,
            'paymentMethod'   => 'creditcard',
            'paymentDetails'  => 'Visa card, ref: xxxxxx-xxxx-xxxx',
            'shippingMethod'  => 'freeshipping',
            'discountAmount'  => 10,
            'couponCode'      => 'XFZDSFSDFSFSD',
            'shippingAmount'  => 5,
            'billingAddress'  => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
                'email'      => 'john.doe@example.com'
            ],
            'shippingAddress' => [
                'firstName'  => 'John',
                'lastName'   => 'Doe',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
                'email'      => 'john.doe@example.com'
            ],
            'items'           => [
                [
                    'product'    => 'msj002',
                    'quantity'   => 1,
                    'price'      => 190.00,
                    'tax'        => 39.90,
                    'totalPrice' => 190.00,
                ],
                [
                    'product'    => 'msj005',
                    'quantity'   => 1,
                    'price'      => 175.00,
                    'tax'        => 36.75,
                    'totalPrice' => 175.00,
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
    public function testGet()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => $this->getReference('marello_order_0')->getId()])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    public function testUpdate()
    {
        $data = [
            'billingAddress'  => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
                'email'      => 'gi.doe@example.com'
            ],
            'shippingAddress' => [
                'firstName'  => 'Han',
                'lastName'   => 'Solo',
                'country'    => 'NL',
                'street'     => 'Hollywood Blvd',
                'city'       => 'Alderaan',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
                'email'      => 'gi.doe@example.com'
            ],
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
