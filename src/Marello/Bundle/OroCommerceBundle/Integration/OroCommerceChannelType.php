<?php

namespace Marello\Bundle\OroCommerceBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class OroCommerceChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'orocommerce';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.orocommerce.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marelloorocommerce/img/oro-commerce-logo.png';
    }
}
