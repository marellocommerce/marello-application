<?php

namespace Marello\Bundle\WebhookBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

/**
 * Activates webhook integration
 */
class WebhookChannel implements ChannelInterface, IconAwareIntegrationInterface
{
    public const TYPE = 'marello_webhook_notification';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return 'marello.webhook.notification.integration.label';
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'bundles/marellowebhook/img/webhook.ico';
    }
}
