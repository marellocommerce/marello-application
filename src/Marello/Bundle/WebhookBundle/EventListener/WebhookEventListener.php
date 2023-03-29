<?php

namespace Marello\Bundle\WebhookBundle\EventListener;

use Marello\Bundle\WebhookBundle\Async\Topics;
use Marello\Bundle\WebhookBundle\Model\WebhookProvider;
use Marello\Bundle\InventoryBundle\Event\BalancedInventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebhookEventListener implements EventSubscriberInterface
{
    /** @var WebhookProvider */
    private $webhookProvider;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;


    public function __construct(
        WebhookProvider $webhookProvider,
        MessageProducerInterface $messageProducer
    ){
        $this->webhookProvider = $webhookProvider;
        $this->messageProducer = $messageProducer;
    }

    public static function getSubscribedEvents()
    {
        //TODO: support more advanced && dynamic/run-time event lists currently not from the enum-lists
        return [
            InventoryUpdateEvent::NAME => "webhookInventoryProcess",
            BalancedInventoryUpdateEvent::BALANCED_UPDATE_AFTER => "webhookInventoryProcess",
        ];
    }

    /**
     * @param $event
     * @throws Exception
     */
    public function webhookInventoryProcess($event): WebhookEventListener
    {
        /** @var InventoryUpdateContext $context */
        $context = null;
        if ($event instanceof InventoryUpdateEvent || $event instanceof BalancedInventoryUpdateEvent) {
            $context = $event->getInventoryUpdateContext();
        }

        //get active inventory webhooks
        $activeWebhookEventLists = $this->webhookProvider->getActiveWebhooks();

        //create messages to process those inventory webhooks
        $subscribedEvents = self::getSubscribedEvents();
        foreach ($activeWebhookEventLists as $activeWebhookEventList) {
            $webhookEventName = $activeWebhookEventList->getEvent()->getName();
            if (array_key_exists($webhookEventName, $subscribedEvents) && $context) {
                $data = [
                    'inventory' => $context->getInventory(),
                    'inventory_level' => $context->getInventoryLevel()->getInventoryQty(),
                    'allocated_inventory_qty' => $context->getValue('allocated_inventory_qty'),
                    'sku' => $context->getInventoryItem()->getProduct()->getSku(),
                    'webhook_id' => $activeWebhookEventList->getId()
                    ];
                $this->sendMessage($data);
            }
        }
        return $this;
    }

    /**
     * send a message to process the webhook vars $context, $activeWebhookEventListId
     * @param array $data
     * @throws Exception
     */
    public function sendMessage(array $data): void
    {
        $this->messageProducer->send(
            Topics::WEBHOOK_NOTIFY,
            $data
        );
    }
}
