<?php

namespace Marello\Bundle\InventoryBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
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
    public function __construct(
        protected InventoryBalancer $inventoryBalancer,
        protected LoggerInterface $logger,
        protected ManagerRegistry $registry
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [ResolveRebalanceInventoryTopic::getName()];
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
            /** @var Product $product */
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
