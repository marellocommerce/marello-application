<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Async\ClearInternalDataForDisabledIntegrationMessage;
use Marello\Bundle\Magento2Bundle\Async\Topics;
use Marello\Bundle\Magento2Bundle\DTO\RemoteDataRemovingDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\Magento2Bundle\Stack\ProductChangesByChannelStack;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;

class IntegrationReverseSyncListenerListener
{
    /** @var string  */
    private const ENABLED_PROPERTY_NAME = 'enabled';

    /** @var RemoteDataRemovingDTO[] */
    protected $remoteDataRemovingDTOs = [];

    /** @var Integration[]  */
    protected $integrationOnClearInternalData = [];

    /** @var ProductRepository  */
    protected $productRepository;

    /** @var ProductChangesByChannelStack */
    protected $changesByChannelStack;

    /** @var MessageProducerInterface */
    protected $producer;

    /**
     * @param ProductRepository $productRepository
     * @param ProductChangesByChannelStack $changesByChannelStack
     * @param MessageProducerInterface $producer
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductChangesByChannelStack $changesByChannelStack,
        MessageProducerInterface $producer
    ) {
        $this->productRepository = $productRepository;
        $this->changesByChannelStack = $changesByChannelStack;
        $this->producer = $producer;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadRemovedIntegrationData($unitOfWork);
        $this->loadDeactivatedIntegrationData($unitOfWork);
    }

    public function onClear(): void
    {
        $this->remoteDataRemovingDTOs = [];
        $this->integrationOnClearInternalData = [];
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws Exception
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->remoteDataRemovingDTOs as $integrationId => $remoteDataRemovingDTO) {
            $this->changesByChannelStack->clearChangesByChannelId($integrationId);
            unset($this->integrationOnClearInternalData[$integrationId]);

            $this
                ->producer
                ->send(
                    Topics::REMOVE_REMOTE_DATA_FOR_DISABLED_INTEGRATION,
                    new Message(
                        $remoteDataRemovingDTO->getMessageBody(),
                        $remoteDataRemovingDTO->isRemovedIntegration() ? MessagePriority::VERY_LOW : null
                    )
                );
        }

        /**
         * Clear info about remote products and links between sales channel and website,
         * to re-sync all existing data after integration will be re-enabled right after website
         * will be linked to existed sales channel
         */
        foreach ($this->integrationOnClearInternalData as $integration) {
            $this->changesByChannelStack->clearChangesByChannelId($integration->getId());

            $this
                ->producer
                ->send(
                    Topics::CLEAR_INTERNAL_DATA_FOR_DISABLED_INTEGRATION,
                    [
                        ClearInternalDataForDisabledIntegrationMessage::INTEGRATION_ID => $integration->getId()
                    ]
                );
        }

        $this->remoteDataRemovingDTOs = [];
        $this->integrationOnClearInternalData = [];
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadRemovedIntegrationData(UnitOfWork $unitOfWork)
    {
        /** @var Integration $entityDeletion */
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entityDeletion) {
            if ($this->isApplicableEntity($entityDeletion)) {
                /** @var Magento2TransportSettings $settingBag */
                $settingBag = $entityDeletion->getTransport()->getSettingsBag();
                if (!$settingBag->isDeleteRemoteDataOnDeletion()) {
                    continue;
                }

                $productIdWithSkuToRemove = $this->productRepository->getOriginalProductIdsWithSKUsByIntegration(
                    $entityDeletion
                );

                $this->remoteDataRemovingDTOs[$entityDeletion->getId()] =
                    new RemoteDataRemovingDTO(
                        $entityDeletion->getId(),
                        RemoteDataRemovingDTO::STATUS_REMOVED,
                        $settingBag,
                        $productIdWithSkuToRemove
                    );
            }
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     */
    protected function loadDeactivatedIntegrationData(UnitOfWork $unitOfWork)
    {
        /** @var Integration $updatedEntity */
        foreach ($unitOfWork->getScheduledEntityUpdates() as $updatedEntity) {
            if ($this->isApplicableEntity($updatedEntity)) {
                if (isset($this->remoteDataRemovingDTOs[$updatedEntity->getId()])) {
                    return;
                }

                $entityChangeSet = $unitOfWork->getEntityChangeSet($updatedEntity);
                if (!\array_key_exists(self::ENABLED_PROPERTY_NAME, $entityChangeSet)) {
                    continue;
                }

                /**
                 * Activation will be processed within website sync
                 */
                if ($updatedEntity->isEnabled()) {
                    continue;
                }

                /** @var Magento2TransportSettings $settingBag */
                $settingBag = $updatedEntity->getTransport()->getSettingsBag();
                if (!$settingBag->isDeleteRemoteDataOnDeactivation()) {
                    $this->integrationOnClearInternalData[$updatedEntity->getId()] = $updatedEntity;

                    continue;
                }

                $productIdWithSkuToRemove = $this->productRepository->getOriginalProductIdsWithSKUsByIntegration(
                    $updatedEntity
                );

                $this->remoteDataRemovingDTOs[$updatedEntity->getId()] =
                    new RemoteDataRemovingDTO(
                        $updatedEntity->getId(),
                        RemoteDataRemovingDTO::STATUS_DEACTIVATED,
                        $settingBag,
                        $productIdWithSkuToRemove
                    );
            }
        }
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isApplicableEntity($entity): bool
    {
        return $entity instanceof Integration && $entity->getType() === Magento2ChannelType::TYPE;
    }
}
