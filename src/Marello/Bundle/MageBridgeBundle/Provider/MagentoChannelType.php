<?php

namespace Marello\Bundle\MageBridgeBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class MagentoChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'magento';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magebridge.magento.channel_type.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marellomagebridge/img/magento-logo.png';
    }
}
