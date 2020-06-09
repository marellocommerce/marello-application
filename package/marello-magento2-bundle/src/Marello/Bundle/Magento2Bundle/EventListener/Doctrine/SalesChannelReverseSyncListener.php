<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Async\SalesChannelsRemovedMessage;
use Marello\Bundle\Magento2Bundle\Async\SalesChannelStateChangedMessage;
use Marello\Bundle\Magento2Bundle\Async\Topics;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelProvider;
use Marello\Bundle\Magento2Bundle\Stack\ChangesByChannelStack;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;

/**
 * @todo
 *
 * 1. Collect info about product ids on remove and update +
 * 2. Implement removing for website, store and product tax class import that doesn't imported (Skip for now)
 *
 *
 *
 * 3. Implement price connector better and skip inventory connector
 * 4. Order import
 * 5. Implement simple converter and listener
 *
 *
 */
class SalesChannelReverseSyncListener
{
    private const ACTIVE_PROPERTY_NAME = 'active';

    /** @var SalesChannelProvider */
    protected $salesChannelInfosProvider;

    /** @var ChangesByChannelStack */
    protected $changesByChannelStack;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var MessageProducerInterface */
    protected $producer;

    /** @var array */
    protected $integrationChannelIdsWithProductIds = [];

    /** @var SalesChannel[] */
    protected $salesChannelsWithUpdatedActiveField = [];

    /**
     * @param SalesChannelProvider $salesChannelInfosProvider
     * @param ChangesByChannelStack $changesByChannelStack
     * @param ProductRepository $productRepository
     * @param MessageProducerInterface $producer
     */
    public function __construct(
        SalesChannelProvider $salesChannelInfosProvider,
        ChangesByChannelStack $changesByChannelStack,
        ProductRepository $productRepository,
        MessageProducerInterface $producer
    ) {
        $this->salesChannelInfosProvider = $salesChannelInfosProvider;
        $this->changesByChannelStack = $changesByChannelStack;
        $this->productRepository = $productRepository;
        $this->producer = $producer;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (empty($this->salesChannelInfosProvider->getSalesChannelsInfoArray(false))) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadSalesChannelsUpdatedInActiveField($unitOfWork);
        $this->loadProductIdsOfRemovedSalesChannels($unitOfWork);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadProductIdsOfRemovedSalesChannels(UnitOfWork $unitOfWork)
    {
        $integrationIdsWithSalesChannelIds = [];
        $removedSalesChannelIds = $this->getRemovedSalesChannelIds($unitOfWork);
        foreach ($removedSalesChannelIds as $removedSalesChannelId) {
            $integrationId = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
                $removedSalesChannelId
            );

            if (null === $integrationId) {
                continue;
            }

            if (!\array_key_exists($integrationId, $integrationIdsWithSalesChannelIds)) {
                $integrationIdsWithSalesChannelIds[$integrationId] = [];
            }

            unset($this->salesChannelsWithUpdatedActiveField[$removedSalesChannelId]);

            $integrationIdsWithSalesChannelIds[$integrationId][$removedSalesChannelId] = $removedSalesChannelId;
        }

        foreach ($integrationIdsWithSalesChannelIds as $integrationId => $salesChannelIds) {
            $this->integrationChannelIdsWithProductIds[$integrationId] = \array_merge(
                $this->productRepository->getProductIdsBySalesChannelIds(\array_values($salesChannelIds)),
                $this->integrationChannelIdsWithProductIds[$integrationId] ?? []
            );
        }
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws Exception
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->salesChannelInfosProvider->clearCache();
        foreach ($this->integrationChannelIdsWithProductIds as $integrationId => $modifiedProductIds) {
            if (empty($modifiedProductIds)) {
                continue;
            }

            $exitedSalesChannelIds = $this->salesChannelInfosProvider->getSalesChannelIdsByIntegrationId(
                $integrationId
            );

            $attachedToIntegrationProducts = $this->productRepository->getProductIdsBySalesChannelIds(
                $exitedSalesChannelIds
            );

            $this->producer->send(
                Topics::SALES_CHANNELS_REMOVED,
                [
                    SalesChannelsRemovedMessage::INTEGRATION_ID => $integrationId,
                    SalesChannelsRemovedMessage::UPDATED_PRODUCT_IDS_KEY => \array_intersect(
                        $modifiedProductIds,
                        $attachedToIntegrationProducts
                    ),
                    SalesChannelsRemovedMessage::REMOVED_PRODUCT_IDS_KEY => \array_diff(
                        $modifiedProductIds,
                        $attachedToIntegrationProducts
                    )
                ]
            );
        }

        $this->salesChannelsWithUpdatedActiveField = [];

        foreach ($this->salesChannelsWithUpdatedActiveField as $salesChannel) {
            $integrationId = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
                $salesChannel->getId(),
                false
            );

            /**
             * Skip when integration is disabled
             */
            if (null === $integrationId) {
                continue;
            }

            $modifiedProductIds = $this->productRepository->getProductIdsBySalesChannelIds([$salesChannel->getId()]);
            if (empty($modifiedProductIds)) {
                continue;
            }

            $integrationSalesChannelIds = $this->salesChannelInfosProvider->getSalesChannelIdsByIntegrationId(
                $integrationId
            );

            $productIdsAttachedToNotChangedChannels = $this->productRepository->getProductIdsBySalesChannelIds(
                \array_diff($integrationSalesChannelIds, [$salesChannel->getId()])
            );

            $createdProductIds = [];
            $updatedProductIds = \array_intersect(
                $modifiedProductIds,
                $productIdsAttachedToNotChangedChannels
            );
            $removedProductIds = [];
            if ($salesChannel->isActive()) {
                $createdProductIds = \array_diff($modifiedProductIds, $productIdsAttachedToNotChangedChannels);
            } else {
                $removedProductIds = \array_diff($modifiedProductIds, $productIdsAttachedToNotChangedChannels);
            }

            $this->producer->send(
                Topics::SALES_CHANNEL_STATE_CHANGED,
                [
                    SalesChannelStateChangedMessage::SALES_CHANNEL_ID_KEY => $salesChannel->getId(),
                    SalesChannelStateChangedMessage::IS_ACTIVE_KEY => $salesChannel->getActive(),
                    SalesChannelStateChangedMessage::INTEGRATION_ID => $integrationId,
                    SalesChannelStateChangedMessage::CREATED_PRODUCT_IDS => $createdProductIds,
                    SalesChannelStateChangedMessage::UPDATED_PRODUCT_IDS => $updatedProductIds,
                    SalesChannelStateChangedMessage::REMOVED_PRODUCT_IDS => $removedProductIds
                ]
            );
        }

        $this->integrationChannelIdsWithProductIds = [];
    }

    /**
     * Clear object storage when error was occurred during UOW#Commit
     *
     * @param OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args)
    {
        $this->salesChannelsWithUpdatedActiveField = [];
        $this->integrationChannelIdsWithProductIds = [];
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @return array
     */
    protected function getRemovedSalesChannelIds(UnitOfWork $unitOfWork): array
    {
        $removedSalesChannelIds = [];

        /** @var SalesChannel $removedSalesChannel */
        foreach ($unitOfWork->getScheduledEntityDeletions() as $removedSalesChannel) {
            if ($this->isEntityApplicable($removedSalesChannel)) {
                $removedSalesChannelIds[$removedSalesChannel->getId()] = true;
            }
        }

        return \array_keys($removedSalesChannelIds);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadSalesChannelsUpdatedInActiveField(UnitOfWork $unitOfWork): void
    {
        /** @var SalesChannel $updatedSalesChannel */
        foreach ($unitOfWork->getScheduledEntityUpdates() as $updatedSalesChannel) {
            if ($this->isEntityApplicable($updatedSalesChannel, false)) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($updatedSalesChannel);
                if (\array_key_exists(self::ACTIVE_PROPERTY_NAME, $entityChangeSet)) {
                    $this->salesChannelsWithUpdatedActiveField[$updatedSalesChannel->getId()] = $updatedSalesChannel;
                }
            }
        }
    }

    /**
     * @param object $entity
     * @param bool $onlyActiveSalesChannel
     * @return bool
     */
    protected function isEntityApplicable($entity, bool $onlyActiveSalesChannel = true): bool
    {
        /** @var $entity SalesChannel */
        if (!$entity instanceof SalesChannel) {
            return false;
        }

        return \array_key_exists(
            $entity->getId(),
            $this->salesChannelInfosProvider->getSalesChannelsInfoArray(
                $onlyActiveSalesChannel
            )
        );
    }
}
