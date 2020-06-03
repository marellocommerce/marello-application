<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\Magento2Bundle\Async\Topics;
use Marello\Bundle\Magento2Bundle\DTO\RemoteDataRemovingDTO;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\Magento2Bundle\Stack\ChangesByChannelStack;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\MessagePriority;

class DisablingIntegrationListener
{
    /** @var string  */
    private const ENABLED_PROPERTY_NAME = 'enabled';

    /** @var RemoteDataRemovingDTO[] */
    protected $remoteDataRemovingDTOs = [];

    /** @var ProductRepository  */
    protected $productRepository;

    /** @var ChangesByChannelStack */
    protected $changesByChannelStack;

    /** @var MessageProducerInterface */
    protected $producer;

    /**
     * @param ProductRepository $productRepository
     * @param ChangesByChannelStack $changesByChannelStack
     * @param MessageProducerInterface $producer
     */
    public function __construct(
        ProductRepository $productRepository,
        ChangesByChannelStack $changesByChannelStack,
        MessageProducerInterface $producer
    ) {
        $this->productRepository = $productRepository;
        $this->changesByChannelStack = $changesByChannelStack;
        $this->producer = $producer;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $this->loadRemovedIntegrationData($unitOfWork);
        $this->loadDeactivatedIntegrationData($unitOfWork);
    }

    public function onClear()
    {
        $this->remoteDataRemovingDTOs = [];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        /**
         * @var RemoteDataRemovingDTO $remoteDataRemovingDTO
         */
        foreach ($this->remoteDataRemovingDTOs as $integrationId => $remoteDataRemovingDTO) {
            $this->changesByChannelStack->clearChangesByChannelId($integrationId);
            if (!$remoteDataRemovingDTO->hasProductsOnRemoteRemove()) {
                continue;
            }

            $this
                ->producer
                ->send(
                    Topics::REMOVE_REMOTE_DATA_FOR_DISABLED_INTEGRATION,
                    new Message(
                        $remoteDataRemovingDTO->getMessageBody(),
                        MessagePriority::VERY_LOW
                    )
                );
        }

        $this->remoteDataRemovingDTOs = [];
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
        /** @var Integration $entityUpdates */
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entityUpdates) {
            if ($this->isApplicableEntity($entityUpdates)) {
                if (isset($this->remoteDataRemovingDTOs[$entityUpdates->getId()])) {
                    return;
                }

                if ($entityUpdates->isEnabled()) {
                    continue;
                }

                $entityChangeSet = $unitOfWork->getEntityChangeSet($entityUpdates);
                if (!\array_key_exists(self::ENABLED_PROPERTY_NAME, $entityChangeSet)) {
                    continue;
                }

                /** @var Magento2TransportSettings $settingBag */
                $settingBag = $entityUpdates->getTransport()->getSettingsBag();
                if (!$settingBag->isDeleteRemoteDataOnDeactivation()) {
                    continue;
                }

                $productIdWithSkuToRemove = $this->productRepository->getOriginalProductIdsWithSKUsByIntegration(
                    $entityUpdates
                );

                $this->remoteDataRemovingDTOs[$entityUpdates->getId()] =
                    new RemoteDataRemovingDTO(
                        $entityUpdates->getId(),
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
