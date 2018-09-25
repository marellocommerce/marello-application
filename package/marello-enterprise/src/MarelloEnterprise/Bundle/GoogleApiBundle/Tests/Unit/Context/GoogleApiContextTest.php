<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Tests\Unit\Context;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContext;

class GoogleApiContextTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $params = [
            GoogleApiContext::FIELD_ORIGIN_ADDRESS => new MarelloAddress(),
            GoogleApiContext::FIELD_DESTINATION_ADDRESS => new MarelloAddress()
        ];

        $context = new GoogleApiContext($params);

        $getterValues = [
            GoogleApiContext::FIELD_ORIGIN_ADDRESS => $context->getOriginAddress(),
            GoogleApiContext::FIELD_DESTINATION_ADDRESS => $context->getDestinationAddress()
        ];

        $this->assertEquals($params, $getterValues);
    }
}
