<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Proxy;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\Magento2Bundle\Provider\SalesChannelInfosProvider;
use Marello\Bundle\Magento2Bundle\Stack\ChangesByChannelStack;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
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

    /** @var ChangesByChannelStack */
    protected $changesByChannelStack;

    /** @var PropertyAccessor $propertyAccessor */
    protected $propertyAccessor;

    /** @var SalesChannelInfosProvider */
    protected $salesChannelInfosProvider;

    /**
     * @param PropertyAccessor $propertyAccessor
     * @param SalesChannelInfosProvider $salesChannelInfosProvider
     * @param ChangesByChannelStack $changesByChannelStack
     */
    public function __construct(
        PropertyAccessor $propertyAccessor,
        SalesChannelInfosProvider $salesChannelInfosProvider,
        ChangesByChannelStack $changesByChannelStack
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->salesChannelInfosProvider = $salesChannelInfosProvider;
        $this->changesByChannelStack = $changesByChannelStack;
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

        $this->loadRemovedProducts($unitOfWork);
        $this->loadCreatedProducts($unitOfWork);
        $this->loadUpdatedProducts($unitOfWork);
        $this->processChangesInSalesChannels($unitOfWork);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadRemovedProducts(UnitOfWork $unitOfWork)
    {
        /** @var Product $entityDeletion */
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entityDeletion) {
            if ($this->isApplicableEntity($entityDeletion, false)) {
                foreach ($this->getAssignedChannelIds($entityDeletion) as $channelId) {
                    $this->changesByChannelStack
                        ->getOrCreateChangesDtoByChannelId($channelId)
                        ->addRemovedProduct($entityDeletion);
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
            if ($this->isApplicableEntity($createdEntity, false)) {
                foreach ($this->getAssignedChannelIds($createdEntity) as $channelId) {
                    $this->changesByChannelStack
                        ->getOrCreateChangesDtoByChannelId($channelId)
                        ->addInsertedProduct($createdEntity);
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
            if ($this->isApplicableEntity($updatedProduct, false)) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($updatedProduct);
                $changedTrackedFieldValues = \array_intersect(
                    $this->mapping['fields'],
                    \array_keys($entityChangeSet)
                );

                if ($changedTrackedFieldValues) {
                    $updatedProductIds[] = $updatedProduct->getId();
                    foreach ($this->getAssignedChannelIds($updatedProduct) as $channelId) {
                        $this->changesByChannelStack
                            ->getOrCreateChangesDtoByChannelId($channelId)
                            ->addUpdatedProduct($updatedProduct);
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
            $prevIntegrationChannelInfoArray = $this->translateSalesChannelsToIntegrationChannelInfoArray(
                $collection->getSnapshot()
            );

            $curIntegrationChannelInfoArray = $this->translateSalesChannelsToIntegrationChannelInfoArray(
                $collection->getValues()
            );

            /** @var Product $owner */
            $owner = $collection->getOwner();

            $addedIntegrationIds = \array_keys(
                \array_diff_key(
                    $curIntegrationChannelInfoArray,
                    $prevIntegrationChannelInfoArray
                )
            );

            \array_walk($addedIntegrationIds, function ($integrationId) use ($owner) {
                $this->changesByChannelStack
                    ->getOrCreateChangesDtoByChannelId($integrationId)
                    ->addAssignedProduct($owner);
            });

            $removedIntegrationIds = \array_keys(
                \array_diff_key(
                    $prevIntegrationChannelInfoArray,
                    $curIntegrationChannelInfoArray
                )
            );

            \array_walk($removedIntegrationIds, function ($integrationId) use ($owner) {
                $this->changesByChannelStack
                    ->getOrCreateChangesDtoByChannelId($integrationId)
                    ->addUnassignedProduct($owner);
            });

            $existedIntegrationIds = \array_keys(
                \array_intersect_key(
                    $prevIntegrationChannelInfoArray,
                    $curIntegrationChannelInfoArray
                )
            );

            $integrationIdsWithUpdatedWebsites = \array_filter(
                $existedIntegrationIds,
                function (int $integrationId) use ($prevIntegrationChannelInfoArray, $curIntegrationChannelInfoArray) {
                    return $prevIntegrationChannelInfoArray[$integrationId] !==
                        $curIntegrationChannelInfoArray[$integrationId];
                }
            );

            \array_walk($integrationIdsWithUpdatedWebsites, function ($integrationId) use ($owner) {
                $this->changesByChannelStack
                    ->getOrCreateChangesDtoByChannelId($integrationId)
                    ->addUpdatedProduct($owner);
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
        $salesChannelInfoArray = $this->salesChannelInfosProvider->getSalesChannelsInfoArray();

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
     * @param $entity
     * @param bool $skipNotInitializedProxy
     * @return bool
     */
    protected function isApplicableEntity($entity, bool $skipNotInitializedProxy = true): bool
    {
        /**
         * Skip non-initialized proxy classes, because it can break flush
         */
        if ($skipNotInitializedProxy && $entity instanceof Proxy && !$entity->__isInitialized()) {
            return false;
        }

        if (!$entity instanceof Product) {
            return false;
        }

        $salesChannelInfoArray = $this->salesChannelInfosProvider->getSalesChannelsInfoArray();

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
        $salesChannelInfoArray = $this->salesChannelInfosProvider->getSalesChannelsInfoArray();

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
     * Tracks changes in default localizable value only
     *
     * @param UnitOfWork $unitOfWork
     * @param array $updatedProductIds
     */
    protected function loadProductsWithUpdatedLocalizableFields(UnitOfWork $unitOfWork, array $updatedProductIds): void
    {
        if (empty($this->mapping['localizableFields'])) {
            return;
        }

        /**
         * Because we can't remove or insert default localizable value,
         * without removing or inserting new product, we check entity updates only
         */
        $defaultLocalizedValues = \array_filter(
            $unitOfWork->getScheduledEntityUpdates(),
            function ($entity) {
                return $entity instanceof LocalizedFallbackValue && null === $entity->getLocalization();
            });

        if (empty($defaultLocalizedValues)) {
            return;
        }

        $products = $this->getApplicableKnownProductEntities($unitOfWork);
        /**
         * Products array without inserted products
         */
        $products = \array_filter($products, function (Product $product) use ($updatedProductIds) {
            return null !== $product->getId() && !\in_array($product->getId(), $updatedProductIds, true);
        });

        /**
         * @var $localizedValue LocalizedFallbackValue
         */
        foreach ($products as $product) {
            if ($this->isProductChangedInLocalizableValue($product, $defaultLocalizedValues)) {
                foreach ($this->getAssignedChannelIds($product) as $channelId) {
                    $this->changesByChannelStack
                        ->getOrCreateChangesDtoByChannelId($channelId)
                        ->addUpdatedProduct($product);
                }
            }
        }
    }

    /**
     * @param Product $product
     * @param array $changedLocalizableValues
     * @return bool
     */
    protected function isProductChangedInLocalizableValue(Product $product, array $changedLocalizableValues): bool
    {
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
     * @return Product[]
     */
    protected function getApplicableKnownProductEntities(UnitOfWork $unitOfWork): array
    {
        $identityMap = $unitOfWork->getIdentityMap();
        $entitiesFromIdentityMap = $identityMap[Product::class] ?? [];

        return \array_filter(
            $entitiesFromIdentityMap,
            [$this, 'isApplicableEntity']
        );
    }
}
