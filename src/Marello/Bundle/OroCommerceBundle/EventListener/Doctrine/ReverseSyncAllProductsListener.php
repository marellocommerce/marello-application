<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\ProductExportBulkDeleteWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\ProductExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceInventoryLevelConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductImageConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductPriceConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxCodeConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxJurisdictionConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxRateConnector;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxRuleConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ReverseSyncAllProductsListener
{
    /**
     * @var MessageProducerInterface
     */
    protected $producer;

    /**
     * @var ProductExportBulkDeleteWriter
     */
    protected $productsBulkDeleteWriter;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param MessageProducerInterface $producer
     * @param ProductExportBulkDeleteWriter $productsBulkDeleteWriter
     */
    public function __construct(
        MessageProducerInterface $producer,
        ProductExportBulkDeleteWriter $productsBulkDeleteWriter
    ) {
        $this->producer = $producer;
        $this->productsBulkDeleteWriter = $productsBulkDeleteWriter;
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $channel = $args->getEntity();
        if ($channel instanceof Channel && $channel->getType() === OroCommerceChannelType::TYPE) {
            $this->entityManager = $args->getEntityManager();
            /** @var OroCommerceSettings $transport */
            $transport = $this->entityManager
                ->getRepository(OroCommerceSettings::class)
                ->find($channel->getTransport()->getId());
            $this->entityManager = $args->getEntityManager();
            $changeSet = $args->getEntityChangeSet();
            $channelId = $channel->getId();
            if (count($changeSet) > 0 && isset($changeSet['enabled'])) {
                if ($changeSet['enabled'][1] === true) {
                    if (true === $transport->isDeleteRemoteDataOnDeactivation()) {
                        $products = $this->getAllProducts($channel->getOrganization());
                        foreach ($products as $product) {
                            $salesChannel = $this->getSalesChannelFromIntegrationChannel($product, $channel);
                            if ($salesChannel !== null) {
                                $this->producer->send(
                                    sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                                    new Message(
                                        [
                                            'integration_id'       => $channel->getId(),
                                            'connector_parameters' => [
                                                AbstractExportWriter::ACTION_FIELD
                                                    => AbstractExportWriter::CREATE_ACTION,
                                                ProductExportCreateReader::SKU_FILTER
                                                    => $product->getSku()
                                            ],
                                            'connector'            => OroCommerceProductConnector::TYPE,
                                            'transport_batch_size' => 100,
                                        ],
                                        MessagePriority::NORMAL
                                    )
                                );
                            }
                        }
                    } elseif (false === $transport->isDeleteRemoteDataOnDeactivation()) {
                        $data = $transport->getData();
                        if (isset($data[AbstractExportWriter::NOT_SYNCHRONIZED])) {
                            $notSynchronizedData = $data[AbstractExportWriter::NOT_SYNCHRONIZED];
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceProductConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceProductImageConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceProductPriceConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceInventoryLevelConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceTaxCodeConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceTaxRateConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceTaxJurisdictionConnector::TYPE
                            );
                            $notSynchronizedData = $this->synchronizeNotSynchronizedData(
                                $channel,
                                $notSynchronizedData,
                                OroCommerceTaxRuleConnector::TYPE
                            );
                            if ($data[AbstractExportWriter::NOT_SYNCHRONIZED] !== $notSynchronizedData) {
                                $data[AbstractExportWriter::NOT_SYNCHRONIZED] = $notSynchronizedData;
                                if (empty($data[AbstractExportWriter::NOT_SYNCHRONIZED])) {
                                    unset($data[AbstractExportWriter::NOT_SYNCHRONIZED]);
                                }
                                $transport->setData($data);
                                $this->entityManager->persist($transport);
                                /** @var OroEventManager $eventManager */
                                $eventManager = $this->entityManager->getEventManager();
                                $eventManager->removeEventListener(
                                    'preUpdate',
                                    'marello_orocommerce.event_listener.doctrine.reverse_sync_product.all'
                                );
                                $this->entityManager->flush($transport);
                            }
                        }
                    }
                } elseif ($changeSet['enabled'][1] === false &&
                    true === $transport->isDeleteRemoteDataOnDeactivation()
                ) {
                    $products = $this->getSynchronizedProducts();
                    $context = new Context(['channel' => $channelId]);
                    $this->productsBulkDeleteWriter->setImportExportContext($context);
                    $this->productsBulkDeleteWriter->write($products);
                }
            }
        }
    }
    
    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $channel = $args->getEntity();
        if ($channel instanceof Channel && $channel->getType() === OroCommerceChannelType::TYPE) {
            /** @var OroCommerceSettings $transport */
            $transport = $channel->getTransport();
            if (true === $transport->isDeleteRemoteDataOnDeactivation()) {
                $this->entityManager = $args->getEntityManager();
                $products = $this->getSynchronizedProducts();
                $context = new Context(['channel' => $channel->getId()]);
                $this->productsBulkDeleteWriter->setImportExportContext($context);
                $this->productsBulkDeleteWriter->write($products);
            }
        }
    }
    
    /**
     * @param Organization $organization
     * @return Product[]
     */
    private function getAllProducts(Organization $organization)
    {
        return $this->entityManager->getRepository(Product::class)->findBy(['organization' => $organization]);
    }

    /**
     * @return Product[]
     */
    private function getSynchronizedProducts()
    {
        return $this->entityManager
            ->getRepository(Product::class)
            ->findByDataKey(ProductExportCreateWriter::PRODUCT_ID_FIELD);
    }

    /**
     * @param Product $product
     * @param Channel $integrationChannel
     * @return SalesChannel|null
     */
    private function getSalesChannelFromIntegrationChannel(Product $product, Channel $integrationChannel)
    {
        foreach ($product->getChannels() as $salesChannel) {
            if ($salesChannel->getIntegrationChannel() === $integrationChannel) {
                return $salesChannel;
            }
        }
        
        return null;
    }

    /**
     * @param Channel $channel
     * @param array $data
     * @param string $connectorType
     * @return array
     */
    private function synchronizeNotSynchronizedData(Channel $channel, array $data, $connectorType)
    {
        if (isset($data[$connectorType])) {
            foreach ($data[$connectorType] as $key => $connector_params) {
                $this->producer->send(
                    sprintf('%s.orocommerce', Topics::REVERS_SYNC_INTEGRATION),
                    new Message(
                        [
                            'integration_id'       => $channel->getId(),
                            'connector_parameters' => $connector_params,
                            'connector'            => $connectorType,
                            'transport_batch_size' => 100,
                        ],
                        MessagePriority::NORMAL
                    )
                );
                unset($data[$connectorType][$key]);
            }
            if (empty($data[$connectorType])) {
                unset($data[$connectorType]);
            }
        }

        return $data;
    }
}
