<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;

class InventoryRebalanceProductProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var InventoryBalancer $inventoryBalancer */
    protected $inventoryBalancer;

    /** @var ManagerRegistry $registry */
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
        return [Topics::RESOLVE_REBALANCE_INVENTORY];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $data = JSON::decode($message->getBody());
        if (! isset($data['product_id'])) {
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

            $this->balanceProduct($product);
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
     * Balance product for Global/Virtual && Fixed warehouses
     * @param Product $product
     */
    private function balanceProduct(Product $product)
    {
        $this->inventoryBalancer->balanceInventory($product, false, true);
        $this->inventoryBalancer->balanceInventory($product, true, true);
    }
}
