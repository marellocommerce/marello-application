<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryQtyAwareInterface;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Event\RemoteProductCreatedEvent;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceInventoryLevelConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class ReverseSyncInventoryLevelListener extends AbstractReverseSyncListener
{
    const SYNC_FIELDS = [
        'inventory',
        'balancedInventory',
    ];

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        parent::init($event->getEntityManager());

        foreach ($this->getEntitiesToSync() as $entity) {
            $this->scheduleSync($entity);
        }
    }

    /**
     * @param RemoteProductCreatedEvent $event
     */
    public function onRemoteProductCreated(RemoteProductCreatedEvent $event)
    {
        $product = $event->getProduct();
        $data = $product->getData();
        $salesChannel = $event->getSalesChannel();
        $balancedInventory = $this->entityManager
            ->getRepository(BalancedInventoryLevel::class)
            ->findExistingBalancedInventory($product, $salesChannel->getGroup());
        if ($balancedInventory) {
            $integrationChannel = $salesChannel->getIntegrationChannel();
            $channelId = $integrationChannel->getId();
            if (isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD]) &&
                isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId]) &&
                $data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId] !== null
            ) {
                if ($integrationChannel->isEnabled()) {
                    $connector_params = [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                        'product' => $product->getId(),
                        'group' => $salesChannel->getGroup()->getId(),
                    ];
                    $this->producer->send(
                        sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                        new Message(
                            [
                                'integration_id'       => $integrationChannel->getId(),
                                'connector_parameters' => $connector_params,
                                'connector'            => OroCommerceInventoryLevelConnector::TYPE,
                                'transport_batch_size' => 100,
                            ],
                            MessagePriority::VERY_HIGH
                        )
                    );
                    // send update for product too as this entity only has a relation to the inventory_status
                    // and needs to be updated...
                    $productConnectorParams = [];
                    $prodData = $product->getData();
                    if (isset($prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                        isset($prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId]) &&
                        $prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId] !== null
                    ) {
                        $productConnectorParams = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                            EntityReaderById::ID_FILTER => $product->getId(),
                        ];
                    }
                    if (!empty($productConnectorParams)) {
                        $this->producer->send(
                            sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                            new Message(
                                [
                                    'integration_id'       => $integrationChannel->getId(),
                                    'connector_parameters' => $productConnectorParams,
                                    'connector'            => OroCommerceProductConnector::TYPE,
                                    'transport_batch_size' => 100,
                                ],
                                MessagePriority::NORMAL
                            )
                        );
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entities = $this->unitOfWork->getScheduledEntityInsertions();
        $entities = array_merge($entities, $this->unitOfWork->getScheduledEntityUpdates());
        $entities = array_merge($entities, $this->unitOfWork->getScheduledEntityDeletions());
        return $this->filterEntities($entities);
    }

    /**
     * @param array $entities
     * @return array
     */
    private function filterEntities(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            if ($entity instanceof SalesChannel) {
                $integrationChannel = $entity->getIntegrationChannel();
                if ($integrationChannel && $integrationChannel->getType() === OroCommerceChannelType::TYPE) {
                    $changeSet = $this->unitOfWork->getEntityChangeSet($entity);
                    if (in_array('group', array_keys($changeSet))) {
                        $group = $changeSet['group'][1];
                        $products = $this->entityManager
                            ->getRepository(Product::class)
                            ->findByChannel($entity);
                        foreach ($products as $product) {
                            $balancedInventory = $this->entityManager
                                ->getRepository(BalancedInventoryLevel::class)
                                ->findExistingBalancedInventory($product, $group);
                            if ($balancedInventory) {
                                $result[
                                sprintf(
                                    '%s_%s',
                                    $product->getSku(),
                                    $group->getId()
                                )
                                ] = $balancedInventory;
                            }
                        }
                    }
                }
            } elseif ($entity instanceof InventoryLevel) {
                $warehouseGroup = $entity->getWarehouse()->getGroup();
                if ($warehouseGroup && !$warehouseGroup->getWarehouseChannelGroupLink()) {
                    if ($this->isSyncRequired($entity)) {
                        /** @var Product $product */
                        $product = $entity->getInventoryItem()->getProduct();
                        foreach ($product->getChannels() as $channel) {
                            if ($channel->getIntegrationChannel()) {
                                $result[sprintf(
                                    '%s_%s',
                                    $product->getSku(),
                                    $channel->getGroup()->getId()
                                )] = $entity;
                            }
                        }
                    }
                }
            } elseif ($entity instanceof BalancedInventoryLevel) {
                if ($this->isSyncRequired($entity)) {
                    $result[
                        sprintf(
                            '%s_%s',
                            $entity->getProduct()->getSku(),
                            $entity->getSalesChannelGroup()->getId()
                        )
                    ] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param InventoryQtyAwareInterface $entity
     * @return bool
     */
    protected function isSyncRequired(InventoryQtyAwareInterface $entity)
    {
        $changeSet = $this->unitOfWork->getEntityChangeSet($entity);

        if (count($changeSet) === 0) {
            return false;
        }

        foreach (array_keys($changeSet) as $fieldName) {
            if (in_array($fieldName, self::SYNC_FIELDS)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param InventoryQtyAwareInterface $entity
     */
    protected function scheduleSync(InventoryQtyAwareInterface $entity)
    {
        if (!in_array($entity, $this->processedEntities)) {
            $product = null;
            $integrationChannels = $this->getIntegrationChannels($entity);
            if ($entity instanceof BalancedInventoryLevel) {
                $product = $entity->getProduct();
            } elseif ($entity instanceof InventoryLevel) {
                $product = $entity->getInventoryItem()->getProduct();
            }
            if ($product && !empty($integrationChannels)) {
                $data = $product->getData();
                foreach ($integrationChannels as $integrationChannel) {
                    $salesChannel = $this->getSalesChannel($product, $integrationChannel);
                    if ($salesChannel) {
                        $channelId = $integrationChannel->getId();
                        if (isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD]) &&
                            isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId]) &&
                            $data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId] !== null
                        ) {
                            $connector_params = [
                                AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                                'product' => $product->getId(),
                                'group' => $salesChannel->getGroup()->getId(),
                            ];
                            if ($entity instanceof InventoryLevel) {
                                $connector_params['entityName'] = InventoryLevel::class;
                            }
                        }

                        if (!empty($connector_params)) {
                            /** @var OroCommerceSettings $transport */
                            $transport = $integrationChannel->getTransport();
                            if ($integrationChannel->isEnabled()) {
                                $this->producer->send(
                                    sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                                    new Message(
                                        [
                                            'integration_id'       => $integrationChannel->getId(),
                                            'connector_parameters' => $connector_params,
                                            'connector'            => OroCommerceInventoryLevelConnector::TYPE,
                                            'transport_batch_size' => 100,
                                        ],
                                        MessagePriority::HIGH
                                    )
                                );
                                // send update for product too as this entity only has a relation to the inventory_status
                                // and needs to be updated...
                                $productConnectorParams = [];
                                $prodData = $product->getData();
                                if (isset($prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                                    isset($prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId]) &&
                                    $prodData[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId] !== null
                                ) {
                                    $productConnectorParams = [
                                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                                        EntityReaderById::ID_FILTER => $product->getId(),
                                    ];
                                }
                                if (!empty($productConnectorParams)) {
                                    $this->producer->send(
                                        sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                                        new Message(
                                            [
                                                'integration_id'       => $integrationChannel->getId(),
                                                'connector_parameters' => $productConnectorParams,
                                                'connector'            => OroCommerceProductConnector::TYPE,
                                                'transport_batch_size' => 100,
                                            ],
                                            MessagePriority::NORMAL
                                        )
                                    );
                                }
                            } elseif (false === $transport->isDeleteRemoteDataOnDeactivation()) {
                                $transportData = $transport->getData();
                                $transportData[AbstractExportWriter::NOT_SYNCHRONIZED]
                                [OroCommerceInventoryLevelConnector::TYPE]
                                [$this->generateConnectionParametersKey($connector_params)] = $connector_params;
                                $transport->setData($transportData);
                                $this->entityManager->persist($transport);
                                /** @var OroEventManager $eventManager */
                                $eventManager = $this->entityManager->getEventManager();
                                $eventManager->removeEventListener(
                                    'onFlush',
                                    'marello_orocommerce.event_listener.doctrine.reverse_sync_inventory_level'
                                );
                            }
    
                            $this->processedEntities[] = $entity;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param InventoryQtyAwareInterface $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(InventoryQtyAwareInterface $entity)
    {
        $integrationChannels = [];
        $salesChannels = [];
        if ($entity instanceof BalancedInventoryLevel) {
            $salesChannels = $entity->getSalesChannelGroup()->getSalesChannels();
        } elseif ($entity instanceof InventoryLevel) {
            $salesChannels = $entity->getInventoryItem()->getProduct()->getChannels();
        }
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)
            ) {
                $connectors = $channel->getConnectors();
                if (in_array(OroCommerceInventoryLevelConnector::TYPE, $connectors)) {
                    $integrationChannels[] = $channel;
                }
            }
        }

        return $integrationChannels;
    }

    /**
     * @param Product $product
     * @param Channel $integrationChannel
     * @return SalesChannel|null
     */
    private function getSalesChannel(Product $product, Channel $integrationChannel)
    {
        foreach ($product->getChannels() as $salesChannel) {
            if ($salesChannel->getIntegrationChannel() === $integrationChannel) {
                return $salesChannel;
            }
        }

        return null;
    }
}
