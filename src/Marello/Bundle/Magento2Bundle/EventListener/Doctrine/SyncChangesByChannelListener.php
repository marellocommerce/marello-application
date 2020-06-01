<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\Magento2Bundle\Batch\Step\ExclusiveItemStep;
use Marello\Bundle\Magento2Bundle\DTO\ChangesByChannelDTO;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Integration\Connector\ProductConnector;
use Marello\Bundle\Magento2Bundle\Stack\ChangesByChannelStack;
use Oro\Component\DependencyInjection\ServiceLink;

class SyncChangesByChannelListener
{
    /** @var ServiceLink */
    protected $syncScheduler;

    /** @var ChangesByChannelStack */
    protected $changesByChannelStack;

    /**
     * @param ChangesByChannelStack $changesByChannelStack
     * @param ServiceLink $syncScheduler
     */
    public function __construct(ChangesByChannelStack $changesByChannelStack, ServiceLink $syncScheduler)
    {
        $this->changesByChannelStack = $changesByChannelStack;
        $this->syncScheduler = $syncScheduler;
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
     * @throws RuntimeException
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
}
