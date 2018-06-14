<?php

namespace Marello\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

use Marello\Bundle\MagentoBundle\Async\Topics;
use Marello\Bundle\MagentoBundle\Provider\Connector\ProductConnector;
use Marello\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductEventListener
{
    use MessageProducerTrait;

    /**
     * ProductEventListener constructor.
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        MessageProducerInterface $messageProducer
    ) {
        $this->setMessageProducer($messageProducer);
    }

    /**
     * @param Product $product
     * @param LifecycleEventArgs $args
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function postPersist(Product $product, LifecycleEventArgs $args)
    {
        $connectorParameters = $this->getConnectorParameters($product, Topics::SYNC_CREATE_ACTION);
        $this->sendMessage($product, ProductConnector::TYPE, $connectorParameters);
    }

    /**
     * @param Product $product
     * @param LifecycleEventArgs $args
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function postUpdate(Product $product, LifecycleEventArgs $args)
    {
        $connectorParameters = $this->getConnectorParameters($product, Topics::SYNC_UPDATE_ACTION);
        $this->sendMessage($product, ProductConnector::TYPE, $connectorParameters);
    }

    /**
     * @param Product $product
     * @param LifecycleEventArgs $args
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function preRemove(Product $product, LifecycleEventArgs $args)
    {
        $connectorParameters = $this->getConnectorParameters($product, Topics::SYNC_REMOVE_ACTION);
        $this->sendMessage($product, ProductConnector::TYPE, $connectorParameters);
    }

    /**
     * @param Product $product
     * @param $action
     * @return array
     */
    protected function getConnectorParameters(Product$product, $action = Topics::SYNC_UPDATE_ACTION)
    {
        return [
            'class'     => Product::class,
            'id'        => $product->getId(),
            'sku'       => $product->getSku(),
            'action'    => $action
        ];
    }

    /**
     * @param Product $product
     * @param string $connector
     * @param string $jobName
     * @return $this
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function sendMessage(
        Product $product,
        $connector,
        $connectorParameters
    ) {
        $integrationIds = $this->getIntegrationChannels($product);

        /** @var MessageProducerInterface $messageProducer */
        $messageProducer = $this->getMessageProducer();

        foreach ($integrationIds as $integrationId) {
            $messageProducer->send(
                Topics::SYNC_PRODUCT_ENTITY_INTEGRATION,
                new Message(
                    [
                        'integration_id'       => $integrationId,
                        'connector_parameters' => $connectorParameters,
                        'connector'            => $connector,
                        'transport_batch_size' => 1,
                    ],
                    MessagePriority::VERY_LOW
                )
            );
        }

        return $this;
    }
    /**
     * @param Product $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(Product $entity)
    {
        $integrationChannels = [];
        $salesChannels = $entity->getChannels();
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === MagentoChannelType::TYPE && $channel->isEnabled()) {
                $integrationChannels[] = $channel->getId();
            }
        }

        return $integrationChannels;
    }
}
