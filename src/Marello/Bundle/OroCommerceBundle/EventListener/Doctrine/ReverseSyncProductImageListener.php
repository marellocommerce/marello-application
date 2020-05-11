<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Event\RemoteProductCreatedEvent;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductImageConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class ReverseSyncProductImageListener extends AbstractReverseSyncListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        parent::init($args->getEntityManager());
        
        $entity = $args->getEntity();
        if ($entity instanceof Product) {
            $changeSet = $this->unitOfWork->getEntityChangeSet($entity);
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
        parent::init($args->getEntityManager());
        
        $entity = $args->getEntity();
        if ($entity instanceof Product) {
            $changeSet = $this->unitOfWork->getEntityChangeSet($entity);
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
     * @param RemoteProductCreatedEvent $event
     */
    public function onRemoteProductCreated(RemoteProductCreatedEvent $event)
    {
        $product = $event->getProduct();
        if ($product->getImage()) {
            $this->scheduleSync($product);
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
                /** @var OroCommerceSettings $transport */
                $transport = $integrationChannel->getTransport();
                $settingsBag = $transport->getSettingsBag();
                if ($integrationChannel->isEnabled()) {
                    $this->producer->send(
                        sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                        new Message(
                            [
                                'integration_id'       => $integrationChannel->getId(),
                                'connector_parameters' => $connector_params,
                                'connector'            => OroCommerceProductImageConnector::TYPE,
                                'transport_batch_size' => 100,
                            ],
                            MessagePriority::HIGH
                        )
                    );
                } elseif ($settingsBag->get(OroCommerceSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION) === false) {
                    $transportData = $transport->getData();
                    $transportData[AbstractExportWriter::NOT_SYNCHRONIZED]
                    [OroCommerceProductImageConnector::TYPE]
                    [$this->generateConnectionParametersKey($connector_params)] = $connector_params;
                    $transport->setData($transportData);
                    /** @var OroEventManager $eventManager */
                    $eventManager = $this->entityManager->getEventManager();
                    $eventManager->removeEventListener(
                        'onFlush',
                        'marello_orocommerce.event_listener.doctrine.reverse_sync_product_image'
                    );
                    $this->entityManager->flush($transport);
                }
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
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)) {
                $connectors = $channel->getConnectors();
                if (in_array(OroCommerceProductImageConnector::TYPE, $connectors)) {
                    $integrationChannels[] = $channel;
                }
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
