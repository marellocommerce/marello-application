<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportCreateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceProductPriceConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\PricingBundle\Entity\BasePrice;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ReverseSyncProductPriceListener extends AbstractReverseSyncListener
{
    const SYNC_FIELDS = [
        'value',
        'currency',
    ];

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        parent::init($event->getEntityManager());

        foreach ($this->getEntitiesToSync() as $entity) {
            $this->scheduleSync($entity);
        }
    }
    
    /**
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entities = $this->unitOfWork->getScheduledEntityInsertions();
        $entities = array_merge($entities, $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates());
        $entities = array_merge($entities, $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions());
        return $this->filterEntities($entities);
    }

    /**
     * @param array $entities
     * @return array
     */
    private function filterEntities(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            if ($entity instanceof Product) {
                $data = $entity->getData();
                if (ReverseSyncProductListener::isSyncRequired($entity, $this->unitOfWork)) {
                    foreach ($entity->getChannels() as $salesChannel) {
                        if ($salesChannel->getIntegrationChannel()) {
                            $finalPrice = $this->getFinalPrice($entity, $salesChannel);
                            if (!isset($data[AbstractProductExportWriter::PRICE_ID_FIELD]) || 
                                    count($data[AbstractProductExportWriter::PRICE_ID_FIELD]) <
                                    count($this->getIntegrationChannels($finalPrice)
                                )
                            ) {
                                $key = sprintf(
                                    '%s_%s',
                                    $entity->getSku(),
                                    $finalPrice->getCurrency()
                                );
                                $result[$key] = $finalPrice;
                            }
                        }
                    }
                }
            }
            if ($entity instanceof ProductPrice || $entity instanceof ProductChannelPrice) {
                if ($this->isSyncRequired($entity)) {
                    $key = sprintf('%s_%s', $entity->getProduct()->getSku(), $entity->getCurrency());
                    if ($entity instanceof ProductChannelPrice) {
                        $key = sprintf('%s_%s', $key, $entity->getChannel()->getId());
                    }
                    $result[$key] = $entity;
                }
            }
        }
        
        usort($result, function ($a, $b) {
            if ($a instanceof ProductChannelPrice && $b instanceof ProductPrice) {
                return -1;
            } elseif ($b instanceof ProductChannelPrice && $a instanceof ProductPrice) {
                return 1;
            } else {
                return 0;
            }
        });

        return $result;
    }

    /**
     * @param ProductPrice|ProductChannelPrice|BasePrice $entity
     * @return bool
     */
    protected function isSyncRequired(BasePrice $entity)
    {
        $changeSet = $this->unitOfWork->getEntityChangeSet($entity);
        
        if (count($changeSet) === 0) {
            return false;
        }

        foreach (array_keys($changeSet) as $fieldName) {
            if (in_array($fieldName, self::SYNC_FIELDS)) {
                $oldValue = $changeSet[$fieldName][0];
                $newValue = $changeSet[$fieldName][1];
                if ($fieldName === 'value') {
                    $oldValue = (float)$oldValue;
                    $newValue = (float)$newValue;
                }
                if ($oldValue !== $newValue) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param BasePrice|ProductPrice|ProductChannelPrice $entity
     */
    protected function scheduleSync(BasePrice $entity)
    {
        if (!in_array($entity, $this->processedEntities)) {
            $integrationChannels = $this->getIntegrationChannels($entity);
            $data = $entity->getProduct()->getData();
            foreach ($integrationChannels as $integrationChannel) {
                $product = $entity->getProduct();
                $salesChannel = $this->getSalesChannel($product, $integrationChannel);
                if ($salesChannel !== null) {
                    $finalPrice = $this->getFinalPrice($product, $salesChannel);
                    if ($entity->getValue() === null || $entity === $finalPrice) {
                        if ($entity instanceof ProductChannelPrice) {
                            $entityName = ProductChannelPrice::class;
                        } else {
                            $entityName = ProductPrice::class;
                        }
                        $channelId = $integrationChannel->getId();
                        if (isset($data[AbstractProductExportWriter::PRICE_ID_FIELD]) &&
                            isset($data[AbstractProductExportWriter::PRICE_ID_FIELD][$channelId]) &&
                            $data[AbstractProductExportWriter::PRICE_ID_FIELD][$channelId] !== null
                        ) {
                            $connector_params = [
                                'processorAlias' => 'marello_orocommerce_product_price.export',
                                AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                                ProductExportCreateReader::SKU_FILTER => $product->getSku(),
                                'value' => $finalPrice->getValue(),
                                'currency' => $finalPrice->getCurrency(),
                            ];
                        } else {
                            $connector_params = [
                                'processorAlias' => 'marello_orocommerce_product_price.export',
                                AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                                ProductExportCreateReader::SKU_FILTER => $product->getSku(),
                                'value' => $finalPrice->getValue(),
                                'currency' => $finalPrice->getCurrency(),
                            ];
                        }

                    if (!empty($connector_params)) {
                        $connector_params['entityName'] = $entityName;
                        $this->syncScheduler->getService()->schedule(
                            $integrationChannel->getId(),
                            OroCommerceProductPriceConnector::TYPE,
                            $connector_params
                        );

                            $this->processedEntities[] = $entity;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param BasePrice $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(BasePrice $entity)
    {
        $integrationChannels = [];
        if ($entity instanceof ProductChannelPrice) {
            $salesChannel = $entity->getChannel();
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled() &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)) {
                $integrationChannels[] = $channel;
            }
        } elseif ($entity instanceof ProductPrice) {
            /** @var SalesChannel[] $salesChannels */
            $salesChannels = $entity->getProduct()->getChannels();
            $integrationChannels = [];
            foreach ($salesChannels as $salesChannel) {
                $channel = $salesChannel->getIntegrationChannel();
                if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled() &&
                    $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)
                ) {
                    $integrationChannels[$channel->getId()] = $channel;
                }
            }
        }

        return $integrationChannels;
    }

    /**
     * @param Product $product
     * @param Channel $integrationChannel
     * @return SalesChannel|null
     */
    private function getSalesChannel(Product $product, Channel $integrationChannel)
    {
        foreach ($product->getChannels() as $salesChannel) {
            if ($salesChannel->getIntegrationChannel() === $integrationChannel) {
                return $salesChannel;
            }
        }
        
        return null;
    }

    /**
     * @param Product $product
     * @param SalesChannel $salesChannel
     * @return BasePrice
     */
    private function getFinalPrice(Product $product, SalesChannel $salesChannel)
    {
        if ($channelPrice = $product->getSalesChannelPrice($salesChannel)) {
            return $channelPrice;
        }

        return $product->getPrice($salesChannel->getCurrency());
    }
}
