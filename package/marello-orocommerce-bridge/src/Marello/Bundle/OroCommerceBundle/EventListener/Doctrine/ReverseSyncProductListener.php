<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReverseSyncProductListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ServiceLink
     */
    private $syncScheduler;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    private $processedEntities = [];

    /**
     * @var array
     */
    protected $syncFields = [
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
     * @param TokenStorageInterface $tokenStorage
     * @param ServiceLink $schedulerServiceLink
     */
    public function __construct(TokenStorageInterface $tokenStorage, ServiceLink $schedulerServiceLink)
    {
        $this->tokenStorage = $tokenStorage;
        $this->syncScheduler = $schedulerServiceLink;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->entityManager = $event->getEntityManager();

        // check for logged user is for confidence that data changes mes from UI, not from sync process.
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }

        foreach ($this->getEntitiesToSync() as $action => $entities) {
            foreach ($entities as $entity) {
                $this->scheduleSync($entity, $action);
            }
        }
    }
    
    /**
     * {@inheritdoc}
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entitiesToInsert = $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions();
        $entitiesToUpdate = $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates();
        $entitiesToDelete = $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions();

        $updatedByProduct = $this->filterEntities(
            $entitiesToUpdate,
            Product::class
        );

        $updatedByProductChannelTaxRelation = $this->filterEntities(
            $entitiesToUpdate,
            ProductChannelTaxRelation::class
        );
        $updated = array_merge($updatedByProduct, $updatedByProductChannelTaxRelation);

        $createdByProduct = $this->filterEntities(
            $entitiesToInsert,
            Product::class
        );

        $createdByProductChannelTaxRelation = $this->filterEntities(
            $entitiesToInsert,
            ProductChannelTaxRelation::class
        );
        foreach ($createdByProductChannelTaxRelation as $code => $entity) {
            if (!in_array($entity, $createdByProduct)) {
                $updated[$code] = $entity;
            }
        }

        $deletedByProduct = $this->filterEntities(
            $entitiesToDelete,
            Product::class
        );

        $deletedByProductChannelTaxRelation = $this->filterEntities(
            $entitiesToDelete,
            ProductChannelTaxRelation::class
        );
        foreach ($deletedByProductChannelTaxRelation as $code => $entity) {
            if (!in_array($entity, $deletedByProduct)) {
                $updated[$code] = $entity;
            }
        }

        return [
            AbstractExportWriter::CREATE_ACTION => $createdByProduct,
            AbstractExportWriter::UPDATE_ACTION => $updated,
            AbstractExportWriter::DELETE_ACTION => $deletedByProduct
        ];
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
                    if ($this->isSyncRequired($entity)) {
                        $result[$entity->getSku()] = $entity;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Product $entity
     * @return bool
     */
    protected function isSyncRequired(Product $entity)
    {
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        if (count($changeSet) === 0) {
            return true;
        }

        foreach (array_keys($changeSet) as $fieldName) {
            if (in_array($fieldName, $this->syncFields)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Schedule a synchronisation based on the action
     * @param Product $entity
     * @param string $action
     */
    protected function scheduleSync(Product $entity, $action)
    {
        if (!in_array($entity, $this->processedEntities)) {
            $integrationChannels = $this->getIntegrationChannels($entity);
            $data = $entity->getData();
            foreach ($integrationChannels as $integrationChannel) {
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
                    $this->syncScheduler->getService()->schedule(
                        $integrationChannel->getId(),
                        OroCommerceProductConnector::TYPE,
                        $connector_params
                    );

                    $this->processedEntities[] = $entity;
                }
            }
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
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled() &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)) {
                $integrationChannels[] = $channel;
            }
        }

        return $integrationChannels;
    }
}
