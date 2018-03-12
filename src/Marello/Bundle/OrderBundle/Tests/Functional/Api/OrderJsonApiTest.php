<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;

class OrderJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'orders';

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Test cget (getting a list of orders) of Order entity
     *
     */
    public function testGetListOfOrders()
    {
        $this->markTestSkipped('Loading Orders with fixtures doesn\'t work properly');
    }

    /**
     * Test get order by id
     */
    public function testGetOrderById()
    {
        $this->markTestSkipped('Loading Orders with fixtures doesn\'t work properly');
    }
}
