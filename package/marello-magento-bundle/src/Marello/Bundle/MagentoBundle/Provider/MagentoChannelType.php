<?php

namespace Marello\Bundle\MagentoBundle\Provider;

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
        return 'marello.magento.channel_type.magento.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marellomagento/img/magento-logo.png';
    }
}
