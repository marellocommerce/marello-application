<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\DependencyInjection\ServiceLink;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceInventoryLevelConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ReverseSyncInventoryLevelListener
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
        'inventory',
        'balancedInventory',
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

        foreach ($this->getEntitiesToSync() as $entity) {
            $this->scheduleSync($entity);
        }
    }
    
    /**
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entities = $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions();
        $entities = array_merge($entities, $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates());
        $entities = array_merge($entities, $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions());
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
            if ($entity instanceof VirtualInventoryLevel) {
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
     * @param VirtualInventoryLevel $entity
     * @return bool
     */
    protected function isSyncRequired(VirtualInventoryLevel $entity)
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

        return true;
    }

    /**
     * @param VirtualInventoryLevel $entity
     */
    protected function scheduleSync(VirtualInventoryLevel $entity)
    {
        if (!in_array($entity, $this->processedEntities)) {
            $integrationChannels = $this->getIntegrationChannels($entity);
            /** @var Product $product */
            $product = $entity->getProduct();
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
                            'group' => $entity->getSalesChannelGroup()->getId(),
                        ];
                    }

                    if (!empty($connector_params)) {
                        $this->syncScheduler->getService()->schedule(
                            $integrationChannel->getId(),
                            OroCommerceInventoryLevelConnector::TYPE,
                            $connector_params
                        );

                        $this->processedEntities[] = $entity;
                    }
                }
            }
        }
    }

    /**
     * @param VirtualInventoryLevel $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(VirtualInventoryLevel $entity)
    {
        $integrationChannels = [];
        $salesChannels = $entity->getSalesChannelGroup()->getSalesChannels();
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled() &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)
            ) {
                $integrationChannels[] = $channel;
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
