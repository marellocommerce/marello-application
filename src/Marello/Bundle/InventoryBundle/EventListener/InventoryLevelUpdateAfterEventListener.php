<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryLevelUpdateAfterEventListener
{
    /** @var MessageProducerInterface $messageProducer */
    private $messageProducer;

    /**
     * VirtualInventoryUpdateAfterEventListener constructor.
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        MessageProducerInterface $messageProducer
    ) {
        $this->messageProducer = $messageProducer;
    }

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     * @return mixed
     */
    public function handleInventoryUpdateAfterEvent(InventoryUpdateEvent $event)
    {
        /** @var InventoryUpdateContext $context */
        $context = $event->getInventoryUpdateContext();
        if ($context->getIsVirtual()) {
            // do nothing when context is for virtual inventory levels
            return;
        }

        // TODO fire inventory log level record creation
        $this->messageProducer->send(
            Topics::INVENTORY_LOG_RECORD_CREATE,
            ['context' => $context, 'jobId' => md5($product->getId())]
        );
    }
}
