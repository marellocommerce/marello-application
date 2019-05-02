<?php

namespace Marello\Bundle\UPSBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class ChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'marello_ups';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.ups.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marelloups/img/ups-logo.gif';
    }
}
