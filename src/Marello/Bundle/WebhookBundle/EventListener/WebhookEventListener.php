<?php

namespace Marello\Bundle\WebhookBundle\EventListener;

use Marello\Bundle\WebhookBundle\Async\Topics;
use Marello\Bundle\WebhookBundle\Entity\Webhook;
use Marello\Bundle\WebhookBundle\Event\WebhookContext;
use Marello\Bundle\WebhookBundle\Event\WebhookEvent;
use Marello\Bundle\WebhookBundle\Integration\Connector\WebhookNotificationConnector;
use Marello\Bundle\WebhookBundle\Model\WebhookProvider;
use Oro\Bundle\MessageQueueBundle\Client\BufferedMessageProducer;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebhookEventListener implements EventSubscriberInterface
{
    /** @var WebhookProvider */
    private WebhookProvider $webhookProvider;

    /**
     * @var MessageProducerInterface
     */
    private MessageProducerInterface $messageProducer;

    /**
     * @param WebhookProvider $webhookProvider
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        WebhookProvider $webhookProvider,
        MessageProducerInterface $messageProducer
    ) {
        $this->webhookProvider = $webhookProvider;
        $this->messageProducer = $messageProducer;
    }

    public static function getSubscribedEvents()
    {
        return [
            WebhookEvent::NAME => 'webhookProcess'
        ];
    }

    /**
     * Handle Inventory events webhook messages
     * @param $event
     * @return WebhookEventListener
     * @throws Exception
     */
    public function webhookProcess($event): WebhookEventListener
    {
        /** @var WebhookContext $context */
        $context = null;
        if ($event instanceof WebhookEvent) {
            $context = $event->getWebhookContext();
        }

        //get active inventory webhooks
        $activeWebhookEventLists =  $context->getWebhooks();
        if (count($activeWebhookEventLists) <= 0) {
            $activeWebhookEventLists = $this->webhookProvider->getActiveWebhooks($context->getEventName());
        }

        //create messages for further processing
        foreach ($activeWebhookEventLists as $activeWebhookEventList) {
            $webhookEventName = $activeWebhookEventList->getEvent();
            if ($context->getEventName() === $webhookEventName) {
                $this->sendWebhookMessage($context, $activeWebhookEventList);
            }
        }
        return $this;
    }

    /**
     * send a message to process the webhook vars $context, $activeWebhookEventListId
     * @param WebhookContext $webhookContext
     * @param Webhook $webhook
     * @throws Exception
     */
    public function sendWebhookMessage(WebhookContext $webhookContext, Webhook $webhook)
    {
        $integrationChannels = $this->webhookProvider->getWebhookIntergrations();
        $items = [
            'items' => $webhookContext->getWebhookDataContext(),
            'webhook_id' => $webhook->getId()
        ];
        foreach ($integrationChannels as $integrationChannel) {
            $this->messageProducer->send(
                Topics::WEBHOOK_NOTIFY,
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
                && $this->messageProducer->isBufferingEnabled()) {
                $this->messageProducer->flushBuffer();
            }
        }
    }
}
