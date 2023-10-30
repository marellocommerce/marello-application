<?php

namespace Marello\Bundle\SalesBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Psr\Log\LoggerInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\SalesBundle\Async\Topic\RebalanceSalesChannelGroupTopic;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class RebalanceSalesChannelGroupProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    use JobIdGenerationTrait;

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
        return [RebalanceSalesChannelGroupTopic::getName()];
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
                        ResolveRebalanceInventoryTopic::getName(),
                        ['product_id' => $productId, 'jobId' => $this->generateJobId($productId)]
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
