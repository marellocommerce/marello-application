<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Marello\Bundle\UPSBundle\DependencyInjection\MarelloUPSExtension;

class MarelloUPSExtensionTest extends ExtensionTestCase
{
    /**
     * @var MarelloUPSExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new MarelloUPSExtension();
    }

    protected function tearDown()
    {
        unset($this->extension);
    }

    public function testLoad()
    {
        $this->loadExtension($this->extension);

        $expectedDefinitions = [
            'marello_ups.provider.channel',
            'marello_ups.provider.transport',
            'marello_ups.form.type.transport_settings',
            'marello_ups.validator.remove_used_shipping_service',
            'marello_ups.entity_listener.channel',
            'marello_ups.entity_listener.transport',
            'marello_ups.disable_integration_listener',
            'marello_ups.client.url_provider_basic',
            'marello_ups.client.factory_basic',
            'marello_ups.connection.validator.request.factory.rate_request',
            'marello_ups.connection.validator.result.factory',
            'marello_ups.connection.validator',
            'marello_ups.handler.action.invalidate_cache',
            'marello_ups.repository.shipping_service',
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
