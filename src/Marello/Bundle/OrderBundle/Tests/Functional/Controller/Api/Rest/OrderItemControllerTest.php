<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\Controller\Api\Rest;


class OrderItemControllerTest
{

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData',
        ]);
    }
}
