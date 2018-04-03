<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Api;

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
        $order = $this->getReference('marello_order_1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $order->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_order_by_id.yml', $response);
    }
}
