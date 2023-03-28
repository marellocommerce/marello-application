<?php

namespace Marello\Bundle\SalesBundle\Async;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Async\Topics as InventoryTopics;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class RebalanceSalesChannelGroupProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MessageProducerInterface $messageProducer,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [Topics::REBALANCE_SALESCHANNEL_GROUP_TOPIC];
    }

    /**
     * @param MessageInterface $message
     * @param SessionInterface $session
     * @return string
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $data = JSON::decode($message->getBody());

        $salesChannels = $data['salesChannelIds'];
        file_put_contents(
            '/app/var/logs/saleschannel.log',
            __METHOD__ . " " . __LINE__ . " " . print_r($data, true). "\r\n",
            FILE_APPEND
        );
        if (!empty($salesChannels)) {
            /** @var ProductRepository $productRepo */
            $productRepo = $this->entityManager->getRepository(Product::class);
            try {
                $productIds = $productRepo->getProductIdsBySalesChannelIds(
                    [$salesChannels],
                    $this->aclHelper
                );

                /** @var array $productIds */
                foreach (array_unique($productIds) as $productId) {
                    $this->messageProducer->send(
                        InventoryTopics::RESOLVE_REBALANCE_INVENTORY,
                        ['product_id' => $productId, 'jobId' => md5($productId)]
                    );
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    'Unexpected exception occurred during Rebalancing for SalesChannels',
                    ['exception' => $e]
                );

                return self::REJECT;
            }

            return self::ACK;
        }

        return self::REJECT;
    }
}
