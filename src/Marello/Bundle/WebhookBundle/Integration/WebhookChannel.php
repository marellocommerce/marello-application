<?php

namespace Marello\Bundle\WebhookBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;

/**
 * Activates webhook integration
 */
class WebhookChannel implements ChannelInterface
{
    public const TYPE = 'marello_webhook';

    public function getLabel(): string
    {
        return 'marello.webhook.notification.integration.label';
    }
}
