<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelProvider;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Component\MessageQueue\Transport\Exception\Exception;

/**
 * Process creating of website with attached sales channel
 */
class WebsiteSalesChannelListener
{
    /** @var string */
    private const SALES_CHANNEL_FIELD_NAME = 'salesChannel';

    /** @var array */
    protected $createdWebsitesPerIntegr = [];

    /** @var array */
    protected $assignedSChPerIntegr = [];

    /** @var array */
    protected $unAssignedSChPerIntegr = [];

    /** @var WebsiteRepository */
    protected $websiteRepository;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var ServiceLink */
    protected $syncScheduler;

    /** @var SalesChannelProvider */
    protected $salesChannelProvider;

    /**
     * @param WebsiteRepository $websiteRepository
     * @param ProductRepository $productRepository
     * @param SalesChannelProvider $salesChannelProvider
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        WebsiteRepository $websiteRepository,
        ProductRepository $productRepository,
        SalesChannelProvider $salesChannelProvider,
        ServiceLink $syncScheduler
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->productRepository = $productRepository;
        $this->salesChannelProvider = $salesChannelProvider;
        $this->syncScheduler = $syncScheduler;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadCreatedWebsites($unitOfWork);
        $this->loadUpdatedWebsites($unitOfWork);
        $this->loadRemovedWebsites($unitOfWork);
    }

    /**
     * Clear object storage when error was occurred during UOW#Commit
     *
     * @param OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args)
    {
        $this->createdWebsitesPerIntegr = [];
        $this->assignedSChPerIntegr = [];
        $this->unAssignedSChPerIntegr = [];
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws Exception
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        /**
         * @var int $integrationId
         * @var Website[] $websites
         */
        foreach ($this->createdWebsitesPerIntegr as $integrationId => $websites) {
            if (isset($this->unAssignedSChPerIntegr[$integrationId]) ||
                isset($this->assignedSChPerIntegr[$integrationId])) {
                continue;
            }

            $isInitialWebsiteSync = $this->isInitialWebsiteSync($integrationId, $websites);

            /**
             * Initial sync
             */
            if ($isInitialWebsiteSync) {
                /**
                 * We should skip future processing of this integration
                 */
                unset($this->createdWebsitesPerIntegr[$integrationId]);
                $applicableWebsites = \array_filter($websites, [$this, 'isApplicableWebsite']);
                if (empty($applicableWebsites)) {
                    return;
                }

                $salesChannelIds = \array_map(function (Website $website) {
                    return $website->getSalesChannel()->getId();
                }, $applicableWebsites);

                $this->processInitialSync($integrationId, $salesChannelIds);
            }
        }

        $this->convertCreatedWebsiteToAssignedSalesChannel();

        $integrationIds = \array_unique(
            \array_merge(
                \array_keys($this->assignedSChPerIntegr),
                \array_keys($this->unAssignedSChPerIntegr)
            )
        );

        $this->salesChannelProvider->clearCache();
        foreach ($integrationIds as $integrationId) {
            $salesChannelIdsChangedAssignedWebsite = \array_keys(
                \array_intersect_key(
                    $this->assignedSChPerIntegr[$integrationId] ?? [],
                    $this->unAssignedSChPerIntegr[$integrationId] ?? []
                )
            );

            $assignedSalesChannelIds = \array_diff(
                \array_keys($this->assignedSChPerIntegr),
                $salesChannelIdsChangedAssignedWebsite
            );

            $this->processAssignSalesChannelsToWebsites($integrationId, $assignedSalesChannelIds);

            $unAssignedSalesChannelIds = \array_diff(
                \array_keys($this->unAssignedSChPerIntegr),
                $salesChannelIdsChangedAssignedWebsite
            );

            $this->processUnAssignSalesChannelsFromWebsites($integrationId, $unAssignedSalesChannelIds);
            $this->processSalesChannelChangedAssignedWebsite($integrationId, $salesChannelIdsChangedAssignedWebsite);
        }

        $this->createdWebsitesPerIntegr = [];
        $this->assignedSChPerIntegr = [];
        $this->unAssignedSChPerIntegr = [];
    }

    /**
     * @param int $integrationId
     * @param array $assignedSalesChannelIds
     */
    protected function processAssignSalesChannelsToWebsites(
        int $integrationId,
        array $assignedSalesChannelIds
    ) {
        $registeredSalesChannelIds = $this->salesChannelProvider->getSalesChannelIdsByIntegrationId($integrationId);
        $productIds = $this->productRepository->getProductIdsBySalesChannelIds($assignedSalesChannelIds);
        foreach ($productIds as $productId) {
            $productSalesChannelIds = $this->productRepository->getSalesChannelIdsByProductId($productId);
            $existedProductSChLinkedToWebsites = \array_intersect(
                \array_diff($productSalesChannelIds, $assignedSalesChannelIds),
                $registeredSalesChannelIds
            );

            if (empty($existedProductSChLinkedToWebsites)) {
                $this->syncScheduler->getService()->schedule(
                    $integrationId,
                    ProductConnector::TYPE,
                    [
                        'ids' => [$productId],
                        ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                            ProductConnector::EXPORT_STEP_CREATE
                    ]
                );

                return;
            }

            $this->syncScheduler->getService()->schedule(
                $integrationId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_UPDATE
                ]
            );
        }
    }

    /**
     * @param int $integrationId
     * @param array $unAssignedSalesChannelIds
     */
    protected function processUnAssignSalesChannelsFromWebsites(int $integrationId, array $unAssignedSalesChannelIds)
    {
        $registeredSalesChannelIds = $this->salesChannelProvider->getSalesChannelIdsByIntegrationId($integrationId);
        $productIds = $this->productRepository->getProductIdsBySalesChannelIds($unAssignedSalesChannelIds);
        foreach ($productIds as $productId) {
            $productSalesChannelIds = $this->productRepository->getSalesChannelIdsByProductId($productId);
            $productSChLinkedToWebsites = \array_intersect($productSalesChannelIds, $registeredSalesChannelIds);

            if (empty($productSChLinkedToWebsites)) {
                $this->syncScheduler->getService()->schedule(
                    $integrationId,
                    ProductConnector::TYPE,
                    [
                        'ids' => [$productId],
                        ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                            ProductConnector::EXPORT_STEP_DELETE_ON_CHANNEL
                    ]
                );

                return;
            }

            $this->syncScheduler->getService()->schedule(
                $integrationId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_UPDATE
                ]
            );
        }
    }

    /**
     * @param int $integrationId
     * @param array $salesChannelIds
     */
    protected function processSalesChannelChangedAssignedWebsite(int $integrationId, array $salesChannelIds)
    {
        $productIds = $this->productRepository->getProductIdsBySalesChannelIds($salesChannelIds);
        foreach ($productIds as $productId) {
            $this->syncScheduler->getService()->schedule(
                $integrationId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_UPDATE
                ]
            );
        }
    }

    /**
     * @param int $integrationId
     * @param array $salesChannelIds
     */
    protected function processInitialSync(int $integrationId, array $salesChannelIds): void
    {
        $productIds = $this->productRepository->getProductIdsBySalesChannelIds($salesChannelIds);
        foreach ($productIds as $productId) {
            $this->syncScheduler->getService()->schedule(
                $integrationId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME =>
                        ProductConnector::EXPORT_STEP_CREATE
                ]
            );
        }
    }

    protected function convertCreatedWebsiteToAssignedSalesChannel(): void
    {
        foreach ($this->createdWebsitesPerIntegr as $integrationId => $websites) {
            /**
             * @var Website $website
             */
            foreach ($websites as $website) {
                if (!$this->isApplicableWebsite($website)) {
                    continue;
                }

                $salesChannel = $website->getSalesChannel();

                if (!\array_key_exists($website->getChannelId(), $this->assignedSChPerIntegr)) {
                    $this->assignedSChPerIntegr[$website->getChannelId()] = [];
                }

                $this->assignedSChPerIntegr[$website->getChannelId()][$salesChannel->getId()] = $salesChannel;
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadCreatedWebsites(UnitOfWork $unitOfWork)
    {
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entityInsertion) {
            if ($entityInsertion instanceof Website && $entityInsertion->getChannelId()) {
                $integrationId = $entityInsertion->getChannelId();
                if (!\array_key_exists($integrationId, $this->createdWebsitesPerIntegr)) {
                    $this->createdWebsitesPerIntegr[$integrationId] = [];
                }

                $this->createdWebsitesPerIntegr[$integrationId][\spl_object_id($entityInsertion)] =
                    $entityInsertion;
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadUpdatedWebsites(UnitOfWork $unitOfWork)
    {
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entityUpdate) {
            if ($entityUpdate instanceof Website && $this->isApplicableWebsite($entityUpdate)) {
                $changeSet = $unitOfWork->getEntityChangeSet($entityUpdate);

                if (!\array_key_exists(self::SALES_CHANNEL_FIELD_NAME, $changeSet)) {
                    continue;
                }

                list($oldValue, $newValue) = $changeSet[self::SALES_CHANNEL_FIELD_NAME];
                $valueableChangeSet = $this->getValueableChangeSetForIntegration($oldValue, $newValue);

                $assignedSalesChannel = $valueableChangeSet['assigned'];
                $unassignedSalesChannel = $valueableChangeSet['unassigned'];

                if ($assignedSalesChannel instanceof SalesChannel) {
                    if (!\array_key_exists(
                        $entityUpdate->getChannelId(),
                        $this->assignedSChPerIntegr
                    )) {
                        $this->assignedSChPerIntegr[$entityUpdate->getChannelId()] = [];
                    }

                    $this->assignedSChPerIntegr[$entityUpdate->getChannelId()][$assignedSalesChannel->getId()] =
                        $assignedSalesChannel;
                }

                if ($unassignedSalesChannel instanceof SalesChannel) {
                    if (!\array_key_exists(
                        $entityUpdate->getChannelId(),
                        $this->unAssignedSChPerIntegr
                    )) {
                        $this->unAssignedSChPerIntegr[$entityUpdate->getChannelId()] = [];
                    }

                    $this->unAssignedSChPerIntegr[$entityUpdate->getChannelId()][$unassignedSalesChannel->getId()] =
                        $unassignedSalesChannel;
                }
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    public function loadRemovedWebsites(UnitOfWork $unitOfWork)
    {
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entityDeletion) {
            if ($entityDeletion instanceof Website &&
                $entityDeletion->getChannelId() &&
                $entityDeletion->getSalesChannel()) {
                $integrationId = $entityDeletion->getChannelId();
                if (!\array_key_exists($integrationId, $this->createdWebsitesPerIntegr)) {
                    $this->createdWebsitesPerIntegr[$integrationId] = [];
                }

                $this->unAssignedSChPerIntegr[$integrationId][$entityDeletion->getSalesChannel()->getId()] =
                    $entityDeletion->getSalesChannel();
            }
        }
    }

    /**
     * @param SalesChannel|null $oldValue
     * @param SalesChannel|null $newValue
     * @return array
     */
    protected function getValueableChangeSetForIntegration(
        SalesChannel $oldValue = null,
        SalesChannel $newValue = null
    ): array
    {
        if ((($oldValue && !$oldValue->isActive()) || $oldValue === null) && $newValue && $newValue->isActive()) {
            return [
                'assigned' => $newValue,
                'unassigned' => false
            ];
        }

        if ($oldValue && $oldValue->isActive() && (($newValue && !$newValue->isActive()) || null === $newValue)) {
            return [
                'assigned' => false,
                'unassigned' => $oldValue
            ];
        }

        if ($oldValue && $oldValue->isActive() && $newValue && $newValue->isActive()) {
            return [
                'assigned' => $newValue,
                'unassigned' => $oldValue
            ];
        }

        return [
            'assigned' => false,
            'unassigned' => false
        ];
    }

    /**
     * @param int $integrationId
     * @param Website[] $websites
     * @return bool
     */
    protected function isInitialWebsiteSync(int $integrationId, array $websites): bool
    {
        $existedWebsiteIds = $this->websiteRepository->getWebsitesIdsByIntegrationId($integrationId);
        $websiteIds = \array_map(function (Website $website) {
            return $website->getId();
        }, $websites);

        sort($existedWebsiteIds);
        sort($websiteIds);

        /**
         * Sign of initial sync
         */
        return $existedWebsiteIds === $websiteIds;
    }

    /**
     * @param Website $website
     * @return bool
     */
    protected function isApplicableWebsite(Website $website): bool
    {
        return $website->getChannelId() && $website->getSalesChannel() && $website->getSalesChannel()->isActive();
    }
}
