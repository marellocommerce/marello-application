<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class ReverseSyncProductListener extends AbstractReverseSyncListener
{
    const ENTITIES_KEY = 'entities';
    const CHANNELS_KEY = 'channels';

    const SYNC_FIELDS = [
        'name',
        'status',
        'sku',
        'warranty',
        'channelPrices',
        'prices',
        'value',
        'taxCode',
    ];

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        parent::init($event->getEntityManager());

        foreach ($this->getEntitiesToSync() as $action => $dataSet) {
            foreach ($dataSet as $data) {
                foreach ($data[self::ENTITIES_KEY] as $entity) {
                    if (isset($data[self::CHANNELS_KEY]) && !empty($data[self::CHANNELS_KEY])) {
                        foreach ($data[self::CHANNELS_KEY] as $channel) {
                            $this->scheduleSync($entity, $action, $channel);
                        }
                    } else {
                        $this->scheduleSync($entity, $action);
                    }
                }
            }
        }
    }
    
    /**
     * {@inheritdoc}
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entitiesToInsert = $this->unitOfWork->getScheduledEntityInsertions();
        $entitiesToUpdate = $this->unitOfWork->getScheduledEntityUpdates();
        $entitiesToDelete = $this->unitOfWork->getScheduledEntityDeletions();

        $created = $this->filterEntities(
            $entitiesToInsert,
            Product::class
        );

        $updated = $this->filterEntities(
            $entitiesToUpdate,
            Product::class
        );

        $deleted = $this->filterEntities(
            $entitiesToDelete,
            Product::class
        );

        $updatedByCreatingProductChannelTaxRelation = $this->filterEntities(
            $entitiesToInsert,
            ProductChannelTaxRelation::class
        );
        foreach ($updatedByCreatingProductChannelTaxRelation as $code => $entity) {
            if (!in_array($entity, $created)) {
                $updated[$code] = $entity;
            }
        }

        $updatedByUpdatingProductChannelTaxRelation = $this->filterEntities(
            $entitiesToUpdate,
            ProductChannelTaxRelation::class
        );
        foreach ($updatedByUpdatingProductChannelTaxRelation as $code => $entity) {
            $updated[$code] = $entity;
        }

        $updatedByDeletingProductChannelTaxRelation = $this->filterEntities(
            $entitiesToDelete,
            ProductChannelTaxRelation::class
        );
        foreach ($updatedByDeletingProductChannelTaxRelation as $code => $entity) {
            if (!in_array($entity, $deleted)) {
                $updated[$code] = $entity;
            }
        }

        $results = [];

        $deletedByRemovingFromSalesChannel = $this->getProductDataRemovedFromIntegrationSalesChannels();
        if (!empty($deletedByRemovingFromSalesChannel) &&
            isset($deletedByRemovingFromSalesChannel[self::ENTITIES_KEY])
        ) {
            foreach ($deletedByRemovingFromSalesChannel[self::ENTITIES_KEY] as $sku => $entity) {
                unset($updated[$sku]);
            }
            $results[AbstractExportWriter::DELETE_ACTION][] = $deletedByRemovingFromSalesChannel;
        }

        if (!empty($created)) {
            $results[AbstractExportWriter::CREATE_ACTION][] = [
                self::ENTITIES_KEY => $created
            ];
        }
        if (!empty($updated)) {
            $results[AbstractExportWriter::UPDATE_ACTION][] = [
                self::ENTITIES_KEY => $updated
            ];
        }
        if (!empty($deleted)) {
            $results[AbstractExportWriter::DELETE_ACTION][] = [
                self::ENTITIES_KEY => $deleted
            ];
        }

        return $results;
    }

    /**
     * @return array
     */
    private function getProductDataRemovedFromIntegrationSalesChannels()
    {
        /** @var PersistentCollection $collection */
        $collectionUpd = $this->unitOfWork->getScheduledCollectionUpdates();
        $result = [];
        foreach ($collectionUpd as $collection) {
            if ($collection->first() instanceof SalesChannel) {
                /** @var SalesChannel $salesChannel */
                foreach ($collection->getDeleteDiff() as $salesChannel) {
                    if ($integrationChannel = $salesChannel->getIntegrationChannel()) {
                        foreach ($this->unitOfWork->getScheduledEntityUpdates() as $entity) {
                            if ($entity instanceof Product) {
                                $result[self::ENTITIES_KEY][$entity->getSku()] = $entity;
                                $result[self::CHANNELS_KEY][$integrationChannel->getId()] = $integrationChannel;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Filter entities by class
     * @param array $entities
     * @param string $class
     * @return array
     */
    private function filterEntities(array $entities, $class)
    {
        $result = [];

        foreach ($entities as $entity) {
            if (is_a($entity, $class)) {
                if ($class === ProductChannelTaxRelation::class) {
                    /** @var Product $product */
                    $product = $entity->getProduct();
                    /** @var SalesChannel $salesChannel */
                    $salesChannel = $entity->getSalesChannel();
                    if ($salesChannel &&
                        $salesChannel->getIntegrationChannel() &&
                        $salesChannel->getIntegrationChannel()->getType() === OroCommerceChannelType::TYPE) {
                        $result[$product->getSku()] = $product;
                    }
                } elseif ($class === Product::class) {
                    if ($this->isSyncRequired($entity, $this->unitOfWork)) {
                        $result[$entity->getSku()] = $entity;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Product $entity
     * @param UnitOfWork $unitOfWork
     * @return bool
     */
    public static function isSyncRequired(Product $entity, UnitOfWork $unitOfWork)
    {
        /** @var PersistentCollection $collection */
        $collectionUpd = $unitOfWork->getScheduledCollectionUpdates();
        foreach ($collectionUpd as $collection) {
            if ($collection->first() instanceof SalesChannel) {
                /** @var SalesChannel $salesChannel */
                foreach ($collection->getInsertDiff() as $salesChannel) {
                    if ($salesChannel->getIntegrationChannel()) {
                        return true;
                    }
                }
            }
        }

        $changeSet = $unitOfWork->getEntityChangeSet($entity);
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
     * Schedule a synchronisation based on the action
     * @param Product $entity
     * @param string $action
     * @param Channel|null $integrationChannel
     */
    protected function scheduleSync(Product $entity, $action, Channel $integrationChannel = null)
    {
        if (!in_array($entity->getSku(), $this->processedEntities)) {
            $data = $entity->getData();
            if ($integrationChannel) {
                $this->scheduleSingleSync($entity, $data, $action, $integrationChannel);
            } else {
                $integrationChannels = $this->getIntegrationChannels($entity);
                foreach ($integrationChannels as $integrationChannel) {
                    $this->scheduleSingleSync($entity, $data, $action, $integrationChannel);
                }
            }
        }
    }

    /**
     * @param Product $entity
     * @param array $data
     * @param string $action
     * @param Channel $integrationChannel
     */
    protected function scheduleSingleSync(Product $entity, Array $data, $action, Channel $integrationChannel)
    {
        $channelId = $integrationChannel->getId();
        $connector_params = [];
        if (AbstractExportWriter::CREATE_ACTION === $action) {
            $connector_params = [
                AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                ProductExportCreateReader::SKU_FILTER => $entity->getSku(),
            ];
        } elseif (AbstractExportWriter::UPDATE_ACTION === $action) {
            if (isset($data[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                isset($data[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId]) &&
                $data[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId] !== null
            ) {
                $connector_params = [
                    AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                    EntityReaderById::ID_FILTER => $entity->getId(),
                ];
            } else {
                $connector_params = [
                    AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                    ProductExportCreateReader::SKU_FILTER => $entity->getSku(),
                ];
            }
        } elseif (AbstractExportWriter::DELETE_ACTION === $action) {
            if (isset($data[AbstractProductExportWriter::PRODUCT_ID_FIELD]) &&
                isset($data[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId]) &&
                $data[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId] !== null
            ) {
                $connector_params = [
                    AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                    ProductExportCreateReader::SKU_FILTER => $entity->getSku(),
                    ProductExportUpdateReader::ID_FILTER =>
                        $data[AbstractProductExportWriter::PRODUCT_ID_FIELD][$channelId],
                ];
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
                            'connector'            => OroCommerceProductConnector::TYPE,
                            'transport_batch_size' => 100,
                        ],
                        MessagePriority::NORMAL
                    )
                );
            } elseif (false === $transport->isDeleteRemoteDataOnDeactivation()) {
                $transportData = $transport->getData();
                $transportData[AbstractExportWriter::NOT_SYNCHRONIZED]
                [OroCommerceProductConnector::TYPE]
                [$this->generateConnectionParametersKey($connector_params)] = $connector_params;
                $transport->setData($transportData);
                $this->entityManager->persist($transport);
                /** @var OroEventManager $eventManager */
                $eventManager = $this->entityManager->getEventManager();
                $eventManager->removeEventListener(
                    'onFlush',
                    'marello_orocommerce.event_listener.doctrine.reverse_sync_product'
                );
                $this->entityManager->flush($transport);
            }

            $this->processedEntities[] = $entity->getSku();
        }
    }

    /**
     * @param Product $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(Product $entity)
    {
        /** @var SalesChannel[] $salesChannels */
        $salesChannels = $entity->getChannels();
        $integrationChannels = [];
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)) {
                $connectors = $channel->getConnectors();
                if (in_array(OroCommerceProductConnector::TYPE, $connectors)) {
                    $integrationChannels[$channel->getId()] = $channel;
                }
            }
        }
        if (empty($integrationChannels)) {
            /** @var PersistentCollection $collection */
            $collectionUpd = $this->unitOfWork->getScheduledCollectionUpdates();
            foreach ($collectionUpd as $collection) {
                if ($collection->first() instanceof SalesChannel) {
                    /** @var SalesChannel $salesChannel */
                    foreach ($collection->getDeleteDiff() as $salesChannel) {
                        if ($channel = $salesChannel->getIntegrationChannel()) {
                            $integrationChannels[$channel->getId()] = $channel;
                        }
                    }
                }
            }
        }

        return $integrationChannels;
    }
}
