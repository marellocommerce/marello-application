<?php

namespace MarelloOroCommerce\src\Marello\Bundle\OroCommerceBundle\Tests\Functional\Controller;

class ConnectionValidationSuccessTest extends AbstractConnectionValidationTest
{
    public function dataProvider()
    {
        return [
            [
                'user' => 'admin',
                'expectedResult' => true,
                'message' => 'OroCommerce Connection is valid'
            ],
        ];
    }
}
