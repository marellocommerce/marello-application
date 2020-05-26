<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Proxy;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\DTO\ChangesByChannelDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ProductReverseSyncListener
{
    /** @var array */
    protected $mapping = [
        'fields' => [
            'status',
            'sku'
        ],
        'localizableFields' => [
            'names'
        ]
    ];

    /** @var string */
    protected $channelsFieldName = 'channels';

    /** @var ChangesByChannelDTO[] */
    protected $changesByChannel = [];

    /** @var ServiceLink */
    protected $syncScheduler;

    /** @var PropertyAccessor $propertyAccessor */
    protected $propertyAccessor;

    /** @var WebsiteRepository */
    protected $websiteRepository;

    /** @var array|null */
    protected $salesChannelsInfoArray;

    /**
     * @param PropertyAccessor $propertyAccessor
     * @param WebsiteRepository $websiteRepository
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        PropertyAccessor $propertyAccessor,
        WebsiteRepository $websiteRepository,
        ServiceLink $syncScheduler
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->websiteRepository = $websiteRepository;
        $this->syncScheduler = $syncScheduler;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (empty($this->getSalesChannelsInfoArray())) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadRemovedProducts($unitOfWork);
        $this->loadCreatedProducts($unitOfWork);
        $this->loadUpdatedProducts($unitOfWork);
        $this->processChangesInSalesChannels($unitOfWork);

        /**
         * @todo Add tracking updated fields with one-to-many and many-to-many relations
         */
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        foreach ($this->changesByChannel as $integrationChannelId => $changesByChannelDTO) {
            $this->scheduleSyncRemovedProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncUnassignedProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncCreateProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncUpdatedProducts($integrationChannelId, $changesByChannelDTO);
        }

        $this->changesByChannel = [];
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadRemovedProducts(UnitOfWork $unitOfWork)
    {
        /** @var Product $entityDeletion */
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entityDeletion) {
            if ($this->isEntityTrackable($entityDeletion)) {
                foreach ($this->getAssignedChannelIds($entityDeletion) as $channelId) {
                    $changesByChannelDTO = $this->getChangesByChannelDTO($channelId);
                    $changesByChannelDTO->addRemovedProduct($entityDeletion);
                }
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadCreatedProducts(UnitOfWork $unitOfWork)
    {
        /** @var Product $createdEntity */
        foreach ($unitOfWork->getScheduledEntityInsertions() as $createdEntity) {
            if ($this->isEntityTrackable($createdEntity)) {
                foreach ($this->getAssignedChannelIds($createdEntity) as $channelId) {
                    $changesByChannelDTO = $this->getChangesByChannelDTO($channelId);
                    $changesByChannelDTO->addInsertedProduct($createdEntity);
                }
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadUpdatedProducts(UnitOfWork $unitOfWork)
    {
        $updatedProductIds = [];

        /** @var Product $updatedProduct */
        foreach ($unitOfWork->getScheduledEntityUpdates() as $updatedProduct) {
            if ($this->isEntityTrackable($updatedProduct)) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($updatedProduct);
                $changedTrackedFieldValues = \array_intersect(
                    $this->mapping['fields'],
                    \array_keys($entityChangeSet)
                );

                if ($changedTrackedFieldValues) {
                    $updatedProductIds[] = $updatedProduct->getId();
                    foreach ($this->getAssignedChannelIds($updatedProduct) as $channelId) {
                        $changesByChannelDTO = $this->getChangesByChannelDTO($channelId);
                        $changesByChannelDTO->addUpdatedProduct($updatedProduct);
                    }
                }
            }
        }

        $this->loadProductsWithUpdatedLocalizableFields($unitOfWork, $updatedProductIds);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function processChangesInSalesChannels(UnitOfWork $unitOfWork)
    {
        $applicableCollectionUpdates = \array_filter(
            $unitOfWork->getScheduledCollectionUpdates(),
            function (PersistentCollection $persistentCollection) {
                if (!$persistentCollection->getOwner() instanceof Product) {
                    return false;
                }

                /** Skip new entities */
                if (null === $persistentCollection->getOwner()->getId()) {
                    return false;
                }

                if ($persistentCollection->getMapping()['fieldName'] !== $this->channelsFieldName) {
                    return false;
                }

                return true;
            }
        );

        /** @var PersistentCollection $collection */
        foreach ($applicableCollectionUpdates as $collection) {
            $previousIntegrationChannelInfoArray = $this->translateSalesChannelsToIntegrationChannelInfoArray(
                $collection->getSnapshot()
            );

            $currentIntegrationChannelInfoArray = $this->translateSalesChannelsToIntegrationChannelInfoArray(
                $collection->getValues()
            );

            /** @var Product $owner */
            $owner = $collection->getOwner();

            $addedIntegrationIds = \array_keys(
                \array_diff_key(
                    $currentIntegrationChannelInfoArray,
                    $previousIntegrationChannelInfoArray
                )
            );

            \array_walk($addedIntegrationIds, function ($integrationId) use ($owner) {
                $this->getChangesByChannelDTO($integrationId)->addAssignedProduct($owner);
            });

            $removedIntegrationIds = \array_keys(
                \array_diff_key(
                    $previousIntegrationChannelInfoArray,
                    $currentIntegrationChannelInfoArray
                )
            );

            \array_walk($removedIntegrationIds, function ($integrationId) use ($owner) {
                $this->getChangesByChannelDTO($integrationId)->addUnassignedProduct($owner);
            });

            $existedIntegrationIds = \array_keys(
                \array_intersect_key(
                    $previousIntegrationChannelInfoArray,
                    $currentIntegrationChannelInfoArray
                )
            );

            $integrationIdsWithUpdatedWebsites = \array_filter(
                $existedIntegrationIds,
                function (int $integrationId) use (
                    $previousIntegrationChannelInfoArray,
                    $currentIntegrationChannelInfoArray
                ) {
                    $changedWebsites = \array_diff(
                        $previousIntegrationChannelInfoArray[$integrationId],
                        $currentIntegrationChannelInfoArray[$integrationId]
                    );

                    return !empty($changedWebsites);
                }
            );

            \array_walk($integrationIdsWithUpdatedWebsites, function ($integrationId) use ($owner) {
                $this->getChangesByChannelDTO($integrationId)->addUpdatedProduct($owner);
            });
        }
    }

    /**
     * @param SalesChannel[] $salesChannels
     * @return array
     * [
     *  int <integration_channel_id> => [
     *      int <assigned_website_id>,...
     *  ] ...
     * ]
     */
    protected function translateSalesChannelsToIntegrationChannelInfoArray(array $salesChannels): array
    {
        $integrationChanelInfoArray = [];
        $salesChannelInfoArray = $this->getSalesChannelsInfoArray();

        foreach ($salesChannels as $salesChannel) {
            $salesChannelInfoDTO = $salesChannelInfoArray[$salesChannel->getId()] ?? null;
            if (null === $salesChannelInfoDTO) {
                continue;
            }

            if (empty($integrationChanelInfoArray[$salesChannelInfoDTO->getIntegrationChannelId()])) {
                $integrationChanelInfoArray[$salesChannelInfoDTO->getIntegrationChannelId()] = [];
            }

            $integrationChanelInfoArray[$salesChannelInfoDTO->getIntegrationChannelId()][] =
                $salesChannelInfoDTO->getWebsiteId();
        }

        return $integrationChanelInfoArray;
    }

    /**
     * @param int $integrationChannelId
     * @return ChangesByChannelDTO
     */
    protected function getChangesByChannelDTO(int $integrationChannelId): ChangesByChannelDTO
    {
        if (empty($this->changesByChannel[$integrationChannelId])) {
            $this->changesByChannel[$integrationChannelId] = new ChangesByChannelDTO($integrationChannelId);
        }

        return $this->changesByChannel[$integrationChannelId];
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isEntityTrackable($entity): bool
    {
        /**
         * Skip non-initialized proxy classes, because it can break flush
         */
        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            return false;
        }

        if (!$entity instanceof Product) {
            return false;
        }

        $salesChannelInfoArray = $this->getSalesChannelsInfoArray();

        $trackedSalesChannels = $entity
            ->getChannels()
            ->filter(function(SalesChannel $salesChannel) use ($salesChannelInfoArray) {
                return \array_key_exists($salesChannel->getId(), $salesChannelInfoArray);
            });

        return false === $trackedSalesChannels->isEmpty();
    }

    /**
     * @param Product $product
     * @return int[]
     */
    protected function getAssignedChannelIds(Product $product): array
    {
        $salesChannelInfoArray = $this->getSalesChannelsInfoArray();

        $integrationChannelIds = [];
        foreach ($product->getChannels() as $channel) {
            $salesChannelInfo = $salesChannelInfoArray[$channel->getId()] ?? null;
            if ($salesChannelInfo instanceof SalesChannelInfo) {
                $integrationChannelIds[$salesChannelInfo->getIntegrationChannelId()] = true;
            }
        }

        return \array_keys($integrationChannelIds);
    }

    /**
     * @return SalesChannelInfo[]
     *
     * [
     *    'sales_channel_id' => SalesChannelInfo <SalesChannelInfo>
     * ]
     */
    protected function getSalesChannelsInfoArray(): array
    {
        if (null === $this->salesChannelsInfoArray) {
            $this->salesChannelsInfoArray = $this->websiteRepository->getSalesChannelInfoArray();
        }

        return $this->salesChannelsInfoArray;
    }

    /**
     * @param int $integrationChannelId
     * @param ChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncRemovedProducts(int $integrationChannelId, ChangesByChannelDTO $changesByChannelDTO)
    {
        foreach ($changesByChannelDTO->getRemovedProductSKUs() as $removedProductSKU) {
            $this->syncScheduler->getService()->schedule(
                $integrationChannelId,
                ProductConnector::TYPE,
                [
                    'skus' => [$removedProductSKU],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME => ProductConnector::EXPORT_STEP_DELETE
                ]
            );
        }
    }

    /**
     * @param int $integrationChannelId
     * @param ChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncUnassignedProducts(int $integrationChannelId, ChangesByChannelDTO $changesByChannelDTO)
    {
        $unassignedProductIds = \array_unique(
            \array_diff(
                $changesByChannelDTO->getUnassignedProductIds(),
                $changesByChannelDTO->getRemovedProductIds()
            )
        );

        foreach ($unassignedProductIds as $unassignedProductId) {
            $this->syncScheduler->getService()->schedule(
                $integrationChannelId,
                ProductConnector::TYPE,
                [
                    'ids' => [$unassignedProductId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME => ProductConnector::EXPORT_STEP_DELETE_ON_CHANNEL
                ]
            );
        }
    }

    /**
     * @param int $integrationChannelId
     * @param ChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncCreateProducts(int $integrationChannelId, ChangesByChannelDTO $changesByChannelDTO)
    {
        $productIdsOnCreate = \array_unique(
            \array_diff(
                \array_merge(
                    $changesByChannelDTO->getInsertedProductIdsWithCountChecking(),
                    $changesByChannelDTO->getAssignedProductIds()
                ),
                $changesByChannelDTO->getUnassignedProductIds(),
                $changesByChannelDTO->getRemovedProductIds()
            )
        );

        foreach ($productIdsOnCreate as $productIdOnCreate) {
            $this->syncScheduler->getService()->schedule(
                $integrationChannelId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productIdOnCreate],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME => ProductConnector::EXPORT_STEP_CREATE
                ]
            );
        }
    }

    /**
     * @param int $integrationChannelId
     * @param ChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncUpdatedProducts(int $integrationChannelId, ChangesByChannelDTO $changesByChannelDTO)
    {
        $updatedProductIds = \array_unique(
            \array_diff(
                $changesByChannelDTO->getUpdatedProductIds(),
                $changesByChannelDTO->getAssignedProductIds(),
                $changesByChannelDTO->getInsertedProductIds(),
                $changesByChannelDTO->getUnassignedProductIds(),
                $changesByChannelDTO->getRemovedProductIds()
            )
        );

        foreach ($updatedProductIds as $productId) {
            $this->syncScheduler->getService()->schedule(
                $integrationChannelId,
                ProductConnector::TYPE,
                [
                    'ids' => [$productId],
                    ExclusiveItemStep::OPTION_KEY_EXCLUSIVE_STEP_NAME => ProductConnector::EXPORT_STEP_UPDATE
                ]
            );
        }
    }

    /**
     * Clear object storage when error was occurred during UOW#Commit
     *
     * @param OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args)
    {
        $this->changesByChannel = [];
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @param array $updatedProductIds
     */
    protected function loadProductsWithUpdatedLocalizableFields(UnitOfWork $unitOfWork, array $updatedProductIds): void
    {
        if (empty($this->mapping['localizableFields'])) {
            return;
        }

        $products = $this->getApplicableKnownProductEntities($unitOfWork);
        /**
         * Products array without inserted products
         */
        $products = \array_filter($products, function (Product $product) use ($updatedProductIds) {
            return null !== $product->getId() && !\in_array($product->getId(), $updatedProductIds, true);
        });

        $defaultLocalizedValues = \array_filter(
            iterator_to_array($this->getScheduledLocalizedValues($unitOfWork)),
            function (LocalizedFallbackValue $localizedValue) {
                return null === $localizedValue->getLocalization();
            });

        /**
         * @var $localizedValue LocalizedFallbackValue
         */
        foreach ($products as $product) {
            if ($this->isProductChangedInLocalizableValues($product, $defaultLocalizedValues)) {
                foreach ($this->getAssignedChannelIds($product) as $channelId) {
                    $changesByChannelDTO = $this->getChangesByChannelDTO($channelId);
                    $changesByChannelDTO->addUpdatedProduct($product);
                }
            }
        }
    }

    /**
     * @param Product $product
     * @param array $changedLocalizableValues
     * @return bool
     */
    protected function isProductChangedInLocalizableValues(
        Product $product,
        array $changedLocalizableValues
    ): bool {
        foreach ($changedLocalizableValues as $localizedValue) {
            foreach ($this->mapping['localizableFields'] as $localizableField) {
                if (!$this->propertyAccessor->isReadable($product, $localizableField)) {
                    continue;
                }

                /** @var PersistentCollection|ArrayCollection $localizableCollection */
                $localizableCollection = $this->propertyAccessor->getValue($product, $localizableField);
                if ($localizableCollection instanceof PersistentCollection &&
                    false === $localizableCollection->isInitialized()) {
                    continue;
                }

                if ($localizableCollection->contains($localizedValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @return \Generator
     */
    protected function getScheduledLocalizedValues(UnitOfWork $unitOfWork)
    {
        /**
         * @todo Refactor this in case when we need to catch non-default changes
         * in Localizable values, in this case we need to make additional query to database
         * or make event on form that can change localizable values of product
         */
//        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
//            if ($entity instanceof LocalizedFallbackValue) {
//                yield $entity;
//            }
//        }
//
//        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
//            if ($entity instanceof LocalizedFallbackValue) {
//                yield $entity;
//            }
//        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LocalizedFallbackValue) {
                yield $entity;
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @return Product[]
     */
    protected function getApplicableKnownProductEntities(UnitOfWork $unitOfWork): array
    {
        $identityMap = $unitOfWork->getIdentityMap();
        $entitiesFromIdentityMap = $identityMap[Product::class] ?? [];

        return \array_filter(
            $entitiesFromIdentityMap,
            [$this, 'isEntityTrackable']
        );
    }
}
