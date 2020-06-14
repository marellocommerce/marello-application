<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\Magento2Bundle\Stack\ProductChangesByChannelStack;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Model\SalesChannelsAwareInterface;

/**
 * Track changes in product related entities
 */
class ProductRelationListener
{
    /** @var \string[][] */
    protected $trackedClassFieldsMapping = [
        BalancedInventoryLevel::class => [
            'inventory'
        ],
        InventoryItem::class => [
            'backorderAllowed'
        ]
    ];

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
        if (false === $this->salesChannelProvider->getSalesChannelsInfoArray()) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadCreatedEntities($unitOfWork);
        $this->loadUpdatedEntities($unitOfWork);
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadCreatedEntities(UnitOfWork $unitOfWork)
    {
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entityInsertion) {
            $entityClassName = ClassUtils::getClass($entityInsertion);
            if (!\array_key_exists($entityClassName, $this->trackedClassFieldsMapping)) {
                continue;
            }

            if ($entityClassName !== BalancedInventoryLevel::class) {
                /** @var Product $product */
                $product = $entityInsertion->getProduct();
                $this->addApplicableProductToStack($product);
            } else {
                $this->addApplicableProductToStackFromBalancedInvLevel($entityInsertion);
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadUpdatedEntities(UnitOfWork $unitOfWork)
    {
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entityUpdates) {
            $entityClassName = ClassUtils::getClass($entityUpdates);
            if (!\array_key_exists($entityClassName, $this->trackedClassFieldsMapping)) {
                continue;
            }

            $trackedFieldNames = $this->trackedClassFieldsMapping[$entityClassName];
            $entityChangeSet = $unitOfWork->getEntityChangeSet($entityUpdates);
            $changedTrackedFieldValues = \array_intersect(
                $trackedFieldNames,
                \array_keys($entityChangeSet)
            );

            if (empty($changedTrackedFieldValues)) {
                continue;
            }

            if ($entityClassName !== BalancedInventoryLevel::class) {
                /** @var Product $product */
                $product = $entityUpdates->getProduct();
                $this->addApplicableProductToStack($product);
            } else {
                $this->addApplicableProductToStackFromBalancedInvLevel($entityUpdates);
            }
        }
    }

    /**
     * @param BalancedInventoryLevel $balancedInvLevel
     */
    protected function addApplicableProductToStackFromBalancedInvLevel(BalancedInventoryLevel $balancedInvLevel): void
    {
        $integration = $balancedInvLevel->getSalesChannelGroup()->getIntegrationChannel();
        if (null === $integration || $integration->getType() !== Magento2ChannelType::TYPE) {
            return;
        }

        /** @var SalesChannelsAwareInterface $product */
        $product = $balancedInvLevel->getProduct();
        if (!$product instanceof SalesChannelsAwareInterface) {
            return;
        }

        $integrationIds = $this->salesChannelProvider->getIntegrationIdsFromSalesChannelAwareEntity($product);
        if (!\in_array($integration->getId(), $integrationIds, true)) {
            return;
        }

        $this->productChangesStack
            ->getOrCreateChangesDtoByChannelId($integration->getId())
            ->addUpdatedProduct($product);
    }

    /**
     * @param Product|null $product
     */
    protected function addApplicableProductToStack(Product $product = null): void
    {
        /**
         * Skip new product entities, because they will be processed in scope of another listener
         */
        if (null === $product || null === $product->getId()) {
            return;
        }

        $integrationIds = $this->salesChannelProvider->getIntegrationIdsFromSalesChannelAwareEntity($product);
        foreach ($integrationIds as $integrationId) {
            $this->productChangesStack
                ->getOrCreateChangesDtoByChannelId($integrationId)
                ->addUpdatedProduct($product);
        }
    }
}
