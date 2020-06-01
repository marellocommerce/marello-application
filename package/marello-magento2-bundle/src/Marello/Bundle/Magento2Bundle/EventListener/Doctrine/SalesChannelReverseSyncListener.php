<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Async\SalesChannelRemovedMessage;
use Marello\Bundle\Magento2Bundle\Async\SalesChannelStateChangedMessage;
use Marello\Bundle\Magento2Bundle\Async\Topics;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelInfosProvider;
use Marello\Bundle\Magento2Bundle\Stack\ChangesByChannelStack;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;

class SalesChannelReverseSyncListener
{
    private const BATCH_SIZE = 100;

    /** @var SalesChannelInfosProvider */
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
     * @param SalesChannelInfosProvider $salesChannelInfosProvider
     * @param ChangesByChannelStack $changesByChannelStack
     * @param ProductRepository $productRepository
     * @param MessageProducerInterface $producer
     */
    public function __construct(
        SalesChannelInfosProvider $salesChannelInfosProvider,
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
        foreach ($this->salesChannelsWithUpdatedActiveField as $salesChannel) {
            $integrationId = $this->salesChannelInfosProvider->getIntegrationIdBySalesChannelId(
                $salesChannel->getId(),
                false
            );

            $this->producer->send(
                Topics::SALES_CHANNEL_STATE_CHANGED,
                [
                    SalesChannelStateChangedMessage::SALES_CHANNEL_ID_KEY => $salesChannel->getId(),
                    SalesChannelStateChangedMessage::IS_ACTIVE_KEY => $salesChannel->getActive(),
                    SalesChannelStateChangedMessage::INTEGRATION_ID => $integrationId
                ]
            );
        }

        foreach ($this->integrationChannelIdsWithProductIds as $integrationId => $productIds) {
            if (empty($productIds)) {
                continue;
            }

            foreach (\array_chunk($productIds, self::BATCH_SIZE) as $chunkProductIds) {
                $this->producer->send(
                    Topics::SALES_CHANNEL_REMOVED,
                    [
                        SalesChannelRemovedMessage::PRODUCT_IDS_KEY => $chunkProductIds,
                        SalesChannelRemovedMessage::INTEGRATION_ID => $integrationId
                    ]
                );
            }
        }

        $this->salesChannelsWithUpdatedActiveField = [];
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
                if (\array_key_exists('active', $entityChangeSet)) {
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
