<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\TaxExportReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxCodeConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ReverseSyncTaxCodeListener extends AbstractReverseSyncListener
{
    const SYNC_FIELDS = [
        'code',
        'description'
    ];

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        parent::init($event->getEntityManager());

        foreach ($this->getEntitiesToSync() as $action => $entities) {
            foreach ($entities as $entity) {
                $this->scheduleSync($entity, $action);
            }
        }
    }
    
    /**
     * @return array
     */
    protected function getEntitiesToSync()
    {
        return [
            AbstractExportWriter::CREATE_ACTION =>
                $this->filterEntities(
                    $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions()
                ),
            AbstractExportWriter::UPDATE_ACTION =>
                $this->filterEntities($this->entityManager->getUnitOfWork()->getScheduledEntityUpdates()),
            AbstractExportWriter::DELETE_ACTION =>
                $this->filterEntities($this->entityManager->getUnitOfWork()->getScheduledEntityDeletions()),
        ];
    }

    /**
     * @param array $entities
     * @return array
     */
    private function filterEntities(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            if ($entity instanceof TaxCode) {
                if ($this->isSyncRequired($entity)) {
                    $result[$entity->getCode()] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param TaxCode $entity
     * @return bool
     */
    protected function isSyncRequired(TaxCode $entity)
    {
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        if (count($changeSet) === 0) {
            return false;
        }
        foreach (array_keys($changeSet) as $fieldName) {
            if (in_array($fieldName, self::SYNC_FIELDS)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TaxCode $entity
     * @param string $action
     */
    protected function scheduleSync(TaxCode $entity, $action)
    {
        if (!in_array($entity, $this->processedEntities)) {
            $integrationChannels = $this->getIntegrationChannels();
            $data = $entity->getData();
            foreach ($integrationChannels as $integrationChannel) {
                $channelId = $integrationChannel->getId();
                $connector_params = [];
                if (AbstractExportWriter::CREATE_ACTION === $action) {
                    $connector_params = [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                        TaxExportReader::CODE_FILTER => $entity->getCode(),
                    ];
                } elseif (AbstractExportWriter::UPDATE_ACTION === $action) {
                    if (isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                        isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId]) &&
                        $data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                            TaxExportReader::CODE_FILTER => $entity->getCode(),
                        ];
                    } else {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                            TaxExportReader::CODE_FILTER => $entity->getCode(),
                        ];
                    }
                } elseif (AbstractExportWriter::DELETE_ACTION === $action) {
                    if (isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                        isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId]) &&
                        $data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                            ProductExportUpdateReader::ID_FILTER =>
                                $data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId],
                        ];
                    }
                }

                if (!empty($connector_params)) {
                    $this->syncScheduler->getService()->schedule(
                        $integrationChannel->getId(),
                        OroCommerceTaxCodeConnector::TYPE,
                        $connector_params
                    );

                    $this->processedEntities[] = $entity;
                }
            }
        }
    }

    /**
     * @return Channel[]
     */
    protected function getIntegrationChannels()
    {
        /** @var Channel[] $channels */
        $channels = $this->entityManager
            ->getRepository(Channel::class)
            ->findBy([
                'type' => OroCommerceChannelType::TYPE,
                'enabled' => true
            ]);

        $integrationChannels = [];
        foreach ($channels as $channel) {
            if ($channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false) &&
                in_array(OroCommerceTaxCodeConnector::TYPE, $channel->getConnectors())) {
                $integrationChannels[] = $channel;
            }
        }

        return $integrationChannels;
    }
}
