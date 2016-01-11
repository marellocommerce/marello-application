<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

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
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData',
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
            'billingAddress'  => [
                'firstName'  => 'Falco',
                'lastName'   => 'van der Maden',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'shippingAddress' => [
                'firstName'  => 'Falco',
                'lastName'   => 'van der Maden',
                'country'    => 'NL',
                'street'     => 'Torenallee 20',
                'city'       => 'Eindhoven',
                'region'     => 'NL-NB',
                'postalCode' => '5617 BC',
            ],
            'items'           => [
                [
                    'product'    => 'msj002',
                    'quantity'   => 1,
                    'price'      => 190.0,
                    'tax'        => 190.0 * 0.2,
                    'totalPrice' => 190.0 * 1.2,
                ],
                [
                    'product'    => 'msj005',
                    'quantity'   => 1,
                    'price'      => 175.0,
                    'tax'        => 175.0 * 0.2,
                    'totalPrice' => 175.0 * 1.2,
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
