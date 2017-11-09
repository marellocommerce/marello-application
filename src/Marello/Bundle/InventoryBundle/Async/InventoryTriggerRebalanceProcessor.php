<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;

class InventoryTriggerRebalanceProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var InventoryBalancer
     */
    protected $inventoryBalancer;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param InventoryBalancer $inventoryBalancer
     * @param LoggerInterface $logger
     * @param ManagerRegistry $registry
     */
    public function __construct(
        InventoryBalancer $inventoryBalancer,
        LoggerInterface $logger,
        ManagerRegistry $registry
    ) {
        $this->inventoryBalancer = $inventoryBalancer;
        $this->logger = $logger;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [
            Topics::RESOLVE_REBALANCE_INVENTORY,
            Topics::RESOLVE_REBALANCE_ALL_INVENTORY
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManagerForClass(Product::class);
        $em->beginTransaction();
        
        $trigger = null;
        try {
            if ($message->getBody() === Topics::ALL_INVENTORY) {
                $products = $em->getRepository(Product::class)->findAll();
                foreach ($products as $product) {
                    $this->balanceProduct($product);
                }
                // send notification afterwards....TODO
            } else {
                $messageData = JSON::decode($message->getBody());
                $product = $em->getRepository(Product::class)->find($messageData);
                $this->balanceProduct($product);
            }

            $em->commit();
        } catch (\InvalidArgumentException $e) {
            $em->rollback();
            $this->logger->error(
                sprintf(
                    'Message is invalid: %s. Original message: "%s"',
                    $e->getMessage(),
                    $message->getBody()
                )
            );

            return self::REJECT;
        } catch (\Exception $e) {
            $em->rollback();
            $this->logger->error(
                'Unexpected exception occurred during Inventory Rebalance',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * Balance product for Global/Virtual && Fixed warehouses
     * @param Product $product
     */
    private function balanceProduct(Product $product)
    {
        $this->inventoryBalancer->balanceInventory($product);
        $this->inventoryBalancer->balanceInventory($product, true);
    }
}
