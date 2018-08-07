<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Api;

use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class OrderJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'orders';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrderData::class
        ]);
    }

    /**
     * Test cget (getting a list of orders) of Order entity
     *
     */
    public function testGetListOfOrders()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(10, $response);
        $this->assertResponseContains('cget_order_list.yml', $response);
    }

    /**
     * Test get order by id
     */
    public function testGetOrderById()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $order->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_order_by_id.yml', $response);
    }

    /**
     * Test get order by orderNumber
     */
    public function testGetOrderByOrderNumber()
    {
        /** @var Order $order */
        $order = $this->getReference('marello_order_1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $order->getId()],
            [
                'filter' => ['orderNumber' =>  $order->getOrderNumber() ]
            ]
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_order_by_orderNumber.yml', $response);
    }


    /**
     * Create a new order
     */
    public function testCreateNewOrder()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'order_create.yml'
        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());
        /** @var Order $order */
        $order = $this->getEntityManager()->find(Order::class, $responseContent->data->id);
        $this->assertEquals($order->getOrderNumber(), $responseContent->data->attributes->orderNumber);
    }
}
