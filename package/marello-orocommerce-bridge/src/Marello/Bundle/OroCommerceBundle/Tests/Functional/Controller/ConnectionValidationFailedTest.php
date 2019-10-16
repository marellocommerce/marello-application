<?php

namespace MarelloOroCommerce\src\Marello\Bundle\OroCommerceBundle\Tests\Functional\Controller;

class ConnectionValidationFailedTest extends AbstractConnectionValidationTest
{
    public function dataProvider()
    {
        return [
            [
                'user' => 'admin1',
                'expectedResult' => false,
                'message' => 'Authorization Failed'
            ],
        ];
    }
}
