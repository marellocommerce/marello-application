<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderWorkflowData;

class OrderJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloorders';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrderData::class,
            LoadOrderWorkflowData::class
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
    public function testCreateNewOrderWithExistingCustomer()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'order_create_with_existing_customer.yml'
        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());
        /** @var Order $order */
        $order = $this->getEntityManager()->find(Order::class, $responseContent->data->id);
        $this->assertCount($order->getItems()->count(), $responseContent->data->relationships->items->data);
    }

    /**
     * Create a new order
     */
    public function testCreateNewOrderWithNewCustomer()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'order_create_with_new_customer.yml'
        );

        $this->assertJsonResponse($response);

        $responseContent = json_decode($response->getContent());
        /** @var Order $order */
        $order = $this->getEntityManager()->find(Order::class, $responseContent->data->id);
        $this->assertEquals($order->getCustomer()->getId(), $responseContent->data->relationships->customer->data->id);

        /** @var Customer $customer */
        $customer = $this->getEntityManager()->find(
            Customer::class,
            $responseContent->data->relationships->customer->data->id
        );
        $this->assertEquals($order->getCustomer()->getEmail(), $customer->getEmail());
        $this->assertNull($customer->getPrimaryAddress());
        $this->assertNull($customer->getShippingAddress());
    }
}
