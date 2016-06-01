<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Marello\Bundle\ShippingBundle\Integration\UPS\UPSApi;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSApiException;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class UPSApiTest extends WebTestCase
{
    /** @var UPSApi */
    protected $api;

    protected function setUp()
    {
        $this->initClient();

        $this->api = $this->client->getContainer()->get('marello_shipping.integration.ups.api');
    }

    /**
     * @test
     * @covers UPSApi::post
     */
    public function apiShouldThrowExceptionWhenEmptyRequestIsSent()
    {
        $this->setExpectedException(
            UPSApiException::class,
            'The request is not well-formed or the operation is not defined. Review for errors before re-submitting.'
        );

        $this->api->post('Ship', []);
    }
}
