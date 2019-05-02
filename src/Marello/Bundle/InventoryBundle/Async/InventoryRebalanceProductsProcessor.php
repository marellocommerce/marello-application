<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class InventoryRebalanceProductsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var ProductRepository $repository */
    protected $repository;

    /** @var MessageProducerInterface $producer */
    protected $producer;

    /**
     * @param MessageProducerInterface $producer
     * @param LoggerInterface $logger
     * @param ProductRepository $repository
     */
    public function __construct(
        MessageProducerInterface $producer,
        LoggerInterface $logger,
        ProductRepository $repository
    ) {
        $this->producer = $producer;
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::RESOLVE_REBALANCE_ALL_INVENTORY];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        if ($message->getBody() !== Topics::ALL_INVENTORY) {
            $this->logger->critical(
                sprintf('Got invalid message. "%s"', $message->getBody()),
                ['message' => $message]
            );

            return self::REJECT;
        }

        $products = $this->repository->findAll();
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $this->producer->send(
                Topics::RESOLVE_REBALANCE_INVENTORY,
                [
                    'product_id' => $product->getId(),
                    'jobId' => md5($product->getId())
                ]
            );
        }

        return self::ACK;
    }
}
