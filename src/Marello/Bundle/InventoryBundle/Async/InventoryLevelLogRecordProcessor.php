<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;

class InventoryLevelLogRecordProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var ManagerRegistry $registry */
    protected $registry;

    /**
     * @param LoggerInterface $logger
     * @param ManagerRegistry $registry
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerRegistry $registry
    ) {
        $this->logger = $logger;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::INVENTORY_LOG_RECORD_CREATE];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        if (! isset($data['context'])) {
            $this->logger->critical(
                sprintf('Got invalid message. "%s"', $message->getBody()),
                ['message' => $message]
            );

            return self::REJECT;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass(Product::class);
        try {
            $product = $em->getRepository(Product::class)->find($data['product_id']);

            if (!$product) {
                $this->logger->error(
                    sprintf(
                        'Product is invalid. Cannot find product with id: "%s"',
                        $data['product_id']
                    )
                );
                return self::REJECT;
            }
            //TODO
//            $this->createLogRecord();
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


    /**
     * @param InventoryLevel    $level                  InventoryLevel to be updated
     * @param string            $trigger                Action that triggered the change
     * @param int|null          $inventoryAlt           Inventory Change qty, qty that represents the actual change
     * @param int|null          $allocatedInventoryAlt  Allocated Inventory Change qty, qty that represents the
     *                                                  actual change
     * @param User|null         $user                   User who triggered the change, if left null,
     *                                                  it is automatically assigned to current one
     * @param mixed|null        $subject                Any entity that should be associated to this operation
     */
    private function createLogRecord(
        InventoryLevel $level,
        $inventoryAlt,
        $allocatedInventoryAlt,
        $trigger,
        $user,
        $subject
    ) {
        if ($inventoryAlt === null) {
            $inventoryAlt = 0;
        }

        if ($allocatedInventoryAlt === null) {
            $allocatedInventoryAlt = 0;
        }

        $record = new InventoryLevelLogRecord(
            $level,
            $inventoryAlt,
            $allocatedInventoryAlt,
            $trigger,
            $user,
            $subject
        );

        $em = $this->registry->getManagerForClass(InventoryLevelLogRecord::class);
        $em->persist($level);
        $em->persist($record);
    }
}
