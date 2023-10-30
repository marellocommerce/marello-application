<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceAllInventoryTopic;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
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
    use JobIdGenerationTrait;

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
        return [ResolveRebalanceAllInventoryTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $products = $this->repository->findAll();
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $this->producer->send(
                ResolveRebalanceInventoryTopic::getName(),
                [
                    'product_id' => $product->getId(),
                    'jobId' => $this->generateJobId($product->getId())
                ]
            );
        }

        return self::ACK;
    }
}
