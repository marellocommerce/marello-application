<?php

namespace Marello\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class Magento2ChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'magento2';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.channel_type.magento2.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/marellomagento/img/magento-logo.png';
    }
}
