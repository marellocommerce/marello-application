<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\Magento2Bundle\Stack\ProductChangesByChannelStack;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

/**
 * Tracks changes in ProductPrice and ProductChannelPrice entities
 */
class ProductPriceListener
{
    private const VALUE_FIELD_NAME = 'value';
    private const PRICE_CHANNEL_FIELD_NAME = 'channel';

    /** @var ProductChangesByChannelStack */
    protected $productChangesStack;

    /** @var TrackedSalesChannelProvider */
    protected $salesChannelProvider;

    /** @var ProductRepository */
    protected $productRepository;

    /**
     * @param ProductChangesByChannelStack $productChangesStack
     * @param TrackedSalesChannelProvider $salesChannelProvider
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductChangesByChannelStack $productChangesStack,
        TrackedSalesChannelProvider $salesChannelProvider,
        ProductRepository $productRepository
    ) {
        $this->productChangesStack = $productChangesStack;
        $this->salesChannelProvider = $salesChannelProvider;
        $this->productRepository = $productRepository;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (false === $this->salesChannelProvider->hasTrackedSalesChannels(false)) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadRemovedAndCreatedEntities($unitOfWork);
        $this->loadUpdatedEntities($unitOfWork);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadRemovedAndCreatedEntities(UnitOfWork $unitOfWork): void
    {
        $entities = \array_merge(
            $unitOfWork->getScheduledEntityDeletions(),
            $unitOfWork->getScheduledEntityInsertions()
        );

        if (empty($entities)) {
            return;
        }

        $salesChannelInfoArray = $this->salesChannelProvider->getSalesChannelsInfoArray();
        $currenciesWSChInfos = $this->salesChannelProvider->getTrackedSalesChannelCurrenciesWithSalesChannelInfos();
        foreach ($entities as $entity) {
            if (($entity instanceof ProductChannelPrice || $entity instanceof ProductPrice) &&
                $entity->getProduct()->getId() === null) {
                continue;
            }

            if ($entity instanceof ProductChannelPrice) {
                $this->processChangesProductChannelPrice(
                    $salesChannelInfoArray,
                    $entity->getChannel(),
                    $entity->getProduct()
                );
            } elseif ($entity instanceof ProductPrice) {
                $this->processChangesInProductPrice(
                    $currenciesWSChInfos,
                    $entity
                );
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadUpdatedEntities(UnitOfWork $unitOfWork): void
    {
        $entities = $unitOfWork->getScheduledEntityUpdates();

        if (empty($entities)) {
            return;
        }

        $salesChannelInfoArray = $this->salesChannelProvider->getSalesChannelsInfoArray();
        $currenciesWSChInfos = $this->salesChannelProvider->getTrackedSalesChannelCurrenciesWithSalesChannelInfos();
        foreach ($entities as $entity) {
            if ($entity instanceof ProductChannelPrice) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($entity);
                if (\array_key_exists(self::PRICE_CHANNEL_FIELD_NAME, $entityChangeSet)) {
                    list($oldSalesChannel, $newSalesChannel) = $entityChangeSet[self::PRICE_CHANNEL_FIELD_NAME];
                    $this->processChangesProductChannelPrice(
                        $salesChannelInfoArray,
                        $oldSalesChannel,
                        $entity->getProduct()
                    );
                    $this->processChangesProductChannelPrice(
                        $salesChannelInfoArray,
                        $newSalesChannel,
                        $entity->getProduct()
                    );
                } elseif (\array_key_exists(self::VALUE_FIELD_NAME, $entityChangeSet)) {
                    $this->processChangesProductChannelPrice(
                        $salesChannelInfoArray,
                        $entity->getChannel(),
                        $entity->getProduct()
                    );
                }
            } elseif ($entity instanceof ProductPrice) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($entity);
                if (\array_key_exists(self::VALUE_FIELD_NAME, $entityChangeSet)) {
                    $this->processChangesInProductPrice($currenciesWSChInfos, $entity);
                }
            }
        }
    }

    /**
     * @param array $salesChannelInfoArray
     * @param SalesChannel $salesChannel
     * @param Product $product
     */
    protected function processChangesProductChannelPrice(
        array $salesChannelInfoArray,
        SalesChannel $salesChannel,
        Product $product
    ): void {
        if (!\array_key_exists($salesChannel->getId(), $salesChannelInfoArray)) {
            return;
        }

        $salesChannelInfo = $salesChannelInfoArray[$salesChannel->getId()];
        $productSalesChannelIds = $this->productRepository->getSalesChannelIdsByProductId(
            $product->getId()
        );

        if (\in_array($salesChannelInfo->getSalesChannelId(), $productSalesChannelIds, true)) {
            $this->productChangesStack
                ->getOrCreateChangesDtoByChannelId($salesChannelInfo->getIntegrationChannelId())
                ->addProductChangesForWebsiteScope(
                    $product,
                    $salesChannelInfo->getWebsiteId()
                );
        }
    }

    /**
     * @param array $currenciesWSChInfos
     * @param ProductPrice $price
     */
    protected function processChangesInProductPrice(
        array $currenciesWSChInfos,
        ProductPrice $price
    ): void {
        $priceCurrency = $price->getCurrency();
        if (!\array_key_exists($priceCurrency, $currenciesWSChInfos)) {
            return;
        }

        $productSalesChannelIds = $this->productRepository->getSalesChannelIdsByProductId(
            $price->getProduct()->getId()
        );

        $salesChannelInfos = \array_intersect_key(
            $currenciesWSChInfos[$priceCurrency],
            \array_flip($productSalesChannelIds)
        );

        foreach ($salesChannelInfos as $salesChannelInfo) {
            $this->productChangesStack
                ->getOrCreateChangesDtoByChannelId($salesChannelInfo->getIntegrationChannelId())
                ->addProductChangesForWebsiteScope(
                    $price->getProduct(),
                    $salesChannelInfo->getWebsiteId()
                );
        }
    }
}
