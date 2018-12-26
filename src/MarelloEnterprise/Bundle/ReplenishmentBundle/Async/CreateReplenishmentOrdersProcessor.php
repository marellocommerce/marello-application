<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

class CreateReplenishmentOrdersProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const TOPIC = 'marelloenterprise_replenishment.create_replenishment_orders';

    const CONFIGS = 'configs';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ReplenishmentOrdersFromConfigProvider
     */
    private $replenishmentOrdersProvider;

    /**
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->replenishmentOrdersProvider = $replenishmentOrdersProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [self::TOPIC];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        $configs = $this->entityManager
            ->getRepository(ReplenishmentOrderConfig::class)
            ->findBy(['id' => $data[self::CONFIGS]]);
        
        try {
            foreach ($configs as $config) {
                $orders = $this->replenishmentOrdersProvider->getReplenishmentOrders($config);
                foreach ($orders as $order) {
                    $this->entityManager->persist($order);
                }
                $config->setExecuted(true);
                $this->entityManager->persist($config);
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during Replenishment Orders creation',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }
}
