<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Oro\Bundle\UserBundle\Entity\UserInterface;

class InventoryBalanceTriggerHandler
{
    protected $messageProducer;
    /**
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(MessageProducerInterface $messageProducer) {
        $this->messageProducer = $messageProducer;
    }
}
