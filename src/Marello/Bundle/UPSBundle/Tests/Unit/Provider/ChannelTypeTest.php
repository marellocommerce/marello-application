<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Provider;

use Marello\Bundle\UPSBundle\Provider\ChannelType;

class ChannelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelType */
    protected $channel;

    protected function setUp()
    {
        $this->channel = new ChannelType();
    }

    public function testGetLabel()
    {
        static::assertInstanceOf('Oro\Bundle\IntegrationBundle\Provider\ChannelInterface', $this->channel);
        static::assertEquals('marello.ups.channel_type.label', $this->channel->getLabel());
    }

    public function testGetIcon()
    {
        static::assertInstanceOf('Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface', $this->channel);
        static::assertEquals('bundles/marelloups/img/ups-logo.gif', $this->channel->getIcon());
    }
}
