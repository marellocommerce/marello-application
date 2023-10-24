<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Doctrine\ORM\EntityManagerInterface;

use Marello\Bundle\InventoryBundle\Async\Topic\BalancedInventoryResetTopic;
use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler;

class BalancedInventoryResetProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var BalancedInventoryHandler $balancedInventoryHandler */
    protected $balancedInventoryHandler;

    /**
     * BalancedInventoryResetProcessor constructor.
     * @param LoggerInterface $logger
     * @param DoctrineHelper $doctrineHelper
     * @param BalancedInventoryHandler $balancedInventoryHandler
     */
    public function __construct(
        LoggerInterface $logger,
        DoctrineHelper $doctrineHelper,
        BalancedInventoryHandler $balancedInventoryHandler
    ) {
        $this->logger = $logger;
        $this->doctrineHelper = $doctrineHelper;
        $this->balancedInventoryHandler = $balancedInventoryHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [BalancedInventoryResetTopic::getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        if (! isset($data['blncd_inventory_level_id'])) {
            $this->logger->critical(
                sprintf('Got invalid message. "%s"', $message->getBody()),
                ['message' => $message]
            );

            return self::REJECT;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(BalancedInventoryLevel::class);
        try {
            /** @var BalancedInventoryLevel $balancedInventoryLevel */
            $balancedInventoryLevel = $this->doctrineHelper
                ->getEntityRepositoryForClass(BalancedInventoryLevel::class)
                ->find($data['blncd_inventory_level_id']);

            if (!$balancedInventoryLevel) {
                $this->logger->error(
                    sprintf(
                        'BalancedInventoryLevel is invalid. Cannot find balanced InventoryLevel with id: "%s"',
                        $data['blncd_inventory_level_id']
                    )
                );
                return self::REJECT;
            }

            $balancedInventoryLevel->setInventoryQty(0);
            $balancedInventoryLevel->setBalancedInventoryQty(0);
            $this->balancedInventoryHandler->saveBalancedInventory($balancedInventoryLevel, true, true);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error(
                sprintf(
                    'Message is invalid: %s. Original message: "%s"',
                    $e->getMessage(),
                    $message->getBody()
                )
            );

            return self::REJECT;
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during Inventory Rebalance',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }
}
