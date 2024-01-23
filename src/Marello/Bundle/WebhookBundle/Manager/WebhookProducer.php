<?php

namespace Marello\Bundle\WebhookBundle\Manager;

use Marello\Bundle\WebhookBundle\Async\Topic\WebhookSyncTopic;
use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Event\WebhookEventInterface;
use Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector;
use Marello\Bundle\WebhookBundle\Model\WebhookContext;
use Oro\Bundle\MessageQueueBundle\Client\BufferedMessageProducer;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class WebhookProducer
{
    public function __construct(
        private WebhookProvider $webhookProvider,
        private MessageProducerInterface $messageProducer
    ) {}

    public function triggerWebhook(WebhookEventInterface $event): void
    {
        $context = $event->getContext();
        $activeWebhookEventLists = $this->webhookProvider->getActiveWebhooks($context->getEventName());

        // Create messages for further processing
        foreach ($activeWebhookEventLists as $activeWebhookEventList) {
            $this->sendWebhookMessage($context, $activeWebhookEventList);
        }
    }

    protected function sendWebhookMessage(WebhookContext $webhookContext, Webhook $webhook)
    {
        $integrationChannels = $this->webhookProvider->getWebhookIntergrations();
        $items = [
            'items' => [$webhookContext->getWebhookDataContext()],
            'webhook_id' => $webhook->getId()
        ];
        foreach ($integrationChannels as $integrationChannel) {
            $this->messageProducer->send(
                WebhookSyncTopic::getName(),
                new Message(
                    [
                        'integration_id' => $integrationChannel->getId(),
                        'transport_batch_size' => 1,
                        'connector' => WebhookNotificationConnector::TYPE,
                        'connector_parameters' => $items
                    ],
                    MessagePriority::NORMAL
                )
            );

            if ($this->messageProducer instanceof BufferedMessageProducer
                && $this->messageProducer->isBufferingEnabled()
            ) {
                $this->messageProducer->flushBuffer();
            }
        }
    }
}
