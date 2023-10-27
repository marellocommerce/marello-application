<?php

namespace Marello\Bundle\WebhookBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookSyncTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_webhook.notify_webhook';
    }

    public static function getDescription(): string
    {
        return 'Processes webhook';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'integration_id',
                'transport_batch_size',
                'connector',
                'connector_parameters',
            ])
            ->setRequired([
                'integration_id',
                'transport_batch_size',
                'connector',
                'connector_parameters',
            ])
            ->addAllowedTypes('integration_id', ['int'])
            ->addAllowedTypes('transport_batch_size', ['int'])
            ->addAllowedTypes('connector', ['string'])
            ->addAllowedTypes('connector_parameters', ['array']);
    }
}
