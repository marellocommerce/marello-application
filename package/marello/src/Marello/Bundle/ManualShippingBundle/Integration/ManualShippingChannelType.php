<?php

namespace Marello\Bundle\ManualShippingBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class ManualShippingChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'manual_shipping';

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'marello.manual_shipping.channel_type.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        return 'bundles/marellomanualshipping/img/manual-shipping-logo.jpg';
    }
}
