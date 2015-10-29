<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    protected $createdId = null;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData',
        ]);
    }

    public function testIndexIsEmpty()
    {
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_orders'),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);

        $this->assertCount(4, json_decode($response->getContent(), true));
    }

    /**
     * @depends testIndexIsEmpty
     */
    public function testCreate()
    {
        $data = [
            'orderReference'  => 333444,
            'salesChannel'    => $this->getReference('marello_sales_channel_0')->getId(),
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
            'subtotal'        => 1000,
            'totalTax'        => 200,
            'grandTotal'      => 1200,
        ];

        $this->client->request(
            'POST',
            $this->getUrl('marello_order_api_post_order'),
            [],
            [],
            $this->generateWsseAuthHeader(),
            json_encode($data)
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $response);

        $this->createdId = $response['id'];
    }

    /**
     * @depends testCreate
     */
    public function testGet()
    {
        $this->initClient([], [], true);
        $this->client->request(
            'GET',
            $this->getUrl('marello_order_api_get_order', ['id' => $this->createdId]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $response = $this->client->getResponse();

        $this->assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    public function testUpdate()
    {

    }
}
