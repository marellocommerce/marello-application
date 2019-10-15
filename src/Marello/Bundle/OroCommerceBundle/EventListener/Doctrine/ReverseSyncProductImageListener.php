<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductImageConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReverseSyncProductImageListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ServiceLink
     */
    private $syncScheduler;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ServiceLink $schedulerServiceLink
     */
    public function __construct(TokenStorageInterface $tokenStorage, ServiceLink $schedulerServiceLink)
    {
        $this->tokenStorage = $tokenStorage;
        $this->syncScheduler = $schedulerServiceLink;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        // check for logged user is for confidence that data changes mes from UI, not from sync process.
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }
        $this->entityManager = $args->getEntityManager();
        $entity = $args->getEntity();
        if ($entity instanceof Product) {
            $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);
            if (in_array('image', array_keys($changeSet))) {
                $this->scheduleSync($entity);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        // check for logged user is for confidence that data changes mes from UI, not from sync process.
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }
        $this->entityManager = $args->getEntityManager();
        $entity = $args->getEntity();
        if ($entity instanceof Product) {
            $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);
            if (in_array('image', array_keys($changeSet))) {
                $this->scheduleSync($entity);
            }
        } elseif ($entity instanceof File) {
            if ($product = $this->getProduct($entity)) {
                $this->scheduleSync($product);
            }
        }
    }

    /**
     * @param Product $product
     */
    public function scheduleSync(Product $product)
    {
        $integrationChannels = $this->getIntegrationChannels($product);
        $data = $product->getData();
        foreach ($integrationChannels as $integrationChannel) {
            $channelId = $integrationChannel->getId();
            $connector_params = [];
            $syncImageId = null;
            if (isset($data[AbstractProductExportWriter::IMAGE_ID_FIELD]) &&
                isset($data[AbstractProductExportWriter::IMAGE_ID_FIELD][$channelId]) &&
                $data[AbstractProductExportWriter::IMAGE_ID_FIELD][$channelId] !== null
            ) {
                $syncImageId = $data[AbstractProductExportWriter::IMAGE_ID_FIELD][$channelId];
            }
            /** @var File $image */
            $image = $product->getImage();
            if ($image && $image->getFilename()) {
                if (!$syncImageId) {
                    $connector_params = [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                        EntityReaderById::ID_FILTER => $image->getId(),
                    ];
                } else {
                    $connector_params = [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                        EntityReaderById::ID_FILTER => $image->getId(),
                    ];
                }
            } else {
                if ($syncImageId) {
                    $connector_params = [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                        ProductExportCreateReader::SKU_FILTER => $product->getSku(),
                        ProductExportUpdateReader::ID_FILTER => $syncImageId,
                    ];
                }
            }

            if (!empty($connector_params)) {
                $this->syncScheduler->getService()->schedule(
                    $integrationChannel->getId(),
                    OroCommerceProductImageConnector::TYPE,
                    $connector_params
                );
            }
        }
    }

    /**
     * @param Product $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(Product $entity)
    {
        /** @var SalesChannel[] $salesChannels */
        $salesChannels = $entity->getChannels();
        $integrationChannels = [];
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled() &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)) {
                $integrationChannels[] = $channel;
            }
        }

        return $integrationChannels;
    }

    /**
     * @param File $entity
     * @return Product|null
     */
    private function getProduct(File $entity)
    {
        return $this->entityManager->getRepository(Product::class)->findOneBy(['image' => $entity]);
    }
}
