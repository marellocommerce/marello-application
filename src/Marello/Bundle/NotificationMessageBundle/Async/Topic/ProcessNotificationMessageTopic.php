<?php

namespace Marello\Bundle\NotificationMessageBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcessNotificationMessageTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_notification_message.process_notification_message';
    }

    public static function getDescription(): string
    {
        return 'Process Queued Notification messages';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'title',
                'message',
                'solution',
                'relatedItemClass',
                'relatedItemId',
                'resolved',
                'alertType',
                'source',
                'operation',
                'step',
                'externalId',
                'log',
                'organization',
                'entity_class',
                'priority',
            ])
            ->setRequired([
                'title',
                'message',
                'entity_class',
            ])
            ->addAllowedTypes('title', ['string'])
            ->addAllowedTypes('message', ['string'])
            ->addAllowedTypes('solution', ['string', 'null'])
            ->addAllowedTypes('relatedItemClass', ['string','null'])
            ->addAllowedTypes('relatedItemId', ['int', 'null'])
            ->addAllowedTypes('resolved', ['string','null'])
            ->addAllowedTypes('alertType', ['string', 'null'])
            ->addAllowedTypes('source', ['string', 'null'])
            ->addAllowedTypes('operation', ['string', 'null'])
            ->addAllowedTypes('step', ['string', 'null'])
            ->addAllowedTypes('externalId', ['int', 'null'])
            ->addAllowedTypes('log', ['string', 'null'])
            ->addAllowedTypes('organization', ['int'])
            ->addAllowedTypes('entity_class', ['string'])
            ->addAllowedTypes('priority', ['string']);
    }
}
