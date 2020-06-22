<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\Magento2Bundle\DTO\ProductChangesByChannelDTO;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Scheduler\ProductSchedulerInterface;
use Marello\Bundle\Magento2Bundle\Stack\ProductChangesByChannelStack;

class ProductChangesByChannelReverseSyncListener
{
    /** @var ProductChangesByChannelStack */
    protected $changesByChannelStack;

    /** @var ProductSchedulerInterface */
    protected $productScheduler;

    /**
     * @param ProductChangesByChannelStack $changesByChannelStack
     * @param ProductSchedulerInterface $productScheduler
     */
    public function __construct(
        ProductChangesByChannelStack $changesByChannelStack,
        ProductSchedulerInterface $productScheduler
    ) {
        $this->changesByChannelStack = $changesByChannelStack;
        $this->productScheduler = $productScheduler;
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        foreach ($this->changesByChannelStack->getChangesDTOs() as $integrationChannelId => $changesByChannelDTO) {
            $this->scheduleSyncRemovedProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncUnassignedProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncCreateProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncUpdatedProducts($integrationChannelId, $changesByChannelDTO);
            $this->scheduleSyncUpdatedWebsiteScopeDataProducts($integrationChannelId, $changesByChannelDTO);
        }

        $this->changesByChannelStack->clearStack();
    }

    /**
     * Clear object storage when error was occurred during UOW#Commit
     *
     * @param OnClearEventArgs $args
     */
    public function onClear(OnClearEventArgs $args)
    {
        $this->changesByChannelStack->clearStack();
    }

    /**
     * @param int $integrationChannelId
     * @param ProductChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncRemovedProducts(
        int $integrationChannelId,
        ProductChangesByChannelDTO $changesByChannelDTO
    ): void  {
        $this->productScheduler->scheduleRemovingProducts(
            $integrationChannelId,
            $changesByChannelDTO->getRemovedProductSKUs()
        );
    }

    /**
     * @param int $integrationChannelId
     * @param ProductChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncUnassignedProducts(
        int $integrationChannelId,
        ProductChangesByChannelDTO $changesByChannelDTO
    ): void  {
        $unassignedProductIds = \array_unique(
            \array_diff(
                $changesByChannelDTO->getUnassignedProductIds(),
                $changesByChannelDTO->getRemovedProductIds()
            )
        );

        $this->productScheduler->scheduleDeleteProductsOnChannel(
            $integrationChannelId,
            $unassignedProductIds
        );
    }

    /**
     * @param int $integrationChannelId
     * @param ProductChangesByChannelDTO $changesByChannelDTO
     * @throws RuntimeException
     */
    protected function scheduleSyncCreateProducts(
        int $integrationChannelId,
        ProductChangesByChannelDTO $changesByChannelDTO
    ): void  {
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

        $this->productScheduler->scheduleCreateProductsOnChannel(
            $integrationChannelId,
            $productIdsOnCreate
        );
    }

    /**
     * @param int $integrationChannelId
     * @param ProductChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncUpdatedProducts(
        int $integrationChannelId,
        ProductChangesByChannelDTO $changesByChannelDTO
    ): void {
        $updatedProductIds = \array_unique(
            \array_diff(
                $changesByChannelDTO->getUpdatedProductIds(),
                $changesByChannelDTO->getAssignedProductIds(),
                $changesByChannelDTO->getInsertedProductIds(),
                $changesByChannelDTO->getUnassignedProductIds(),
                $changesByChannelDTO->getRemovedProductIds()
            )
        );

        $this->productScheduler->scheduleUpdateProductsOnChannel(
            $integrationChannelId,
            $updatedProductIds
        );
    }

    /**
     * @param int $integrationChannelId
     * @param ProductChangesByChannelDTO $changesByChannelDTO
     */
    protected function scheduleSyncUpdatedWebsiteScopeDataProducts(
        int $integrationChannelId,
        ProductChangesByChannelDTO $changesByChannelDTO
    ): void {
        $productChangesForWebsiteScopeArray = $changesByChannelDTO->getProductChangesForWebsiteScopeArray();
        foreach ($productChangesForWebsiteScopeArray as $productChangesForWebsiteScope) {
            $updatedProductIds = \array_unique(
                \array_diff(
                    $productChangesForWebsiteScope->getUpdatedProductIds(),
                    $changesByChannelDTO->getAssignedProductIds(),
                    $changesByChannelDTO->getInsertedProductIds(),
                    $changesByChannelDTO->getUnassignedProductIds(),
                    $changesByChannelDTO->getRemovedProductIds()
                )
            );

            $this->productScheduler->scheduleUpdateWebsiteScopeDataProductsOnChannel(
                $integrationChannelId,
                $productChangesForWebsiteScope->getWebsiteId(),
                $updatedProductIds
            );
        }
    }
}
