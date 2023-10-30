<?php

namespace Marello\Bundle\WebhookBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebhookSyncTopic extends AbstractTopic implements JobAwareTopicInterface
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

    public function createJobName($messageBody): string
    {
        return sprintf('%s:%s_%s', self::getName(), $messageBody['integration_id'], uniqid('', true));
    }
}
