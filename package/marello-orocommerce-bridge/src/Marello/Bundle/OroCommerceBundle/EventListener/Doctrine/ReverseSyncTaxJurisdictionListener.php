<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\TaxExportReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxJurisdictionExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxJurisdictionConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\ZipCode;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class ReverseSyncTaxJurisdictionListener extends AbstractReverseSyncListener
{
    const SYNC_FIELDS = [
        'code',
        'description',
        'country',
        'region',
        'regionText',
        'zipCode',
        'zipRangeStart',
        'zipRangeEnd'
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
        $updatedByJurisdiction = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates(),
            TaxJurisdiction::class
        );
        $updatedByZipCode = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates(),
            ZipCode::class
        );
        $updated = array_merge($updatedByJurisdiction, $updatedByZipCode);

        $createdByJurisdiction = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions(),
            TaxJurisdiction::class
        );
        $createdByZipCode = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions(),
            ZipCode::class
        );
        foreach ($createdByZipCode as $code => $entity) {
            if (!in_array($entity, $createdByJurisdiction)) {
                $updated[$code] = $entity;
            }
        }

        $deletedByJurisdiction = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions(),
            TaxJurisdiction::class
        );
        $deletedByZipCode = $this->filterEntities(
            $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions(),
            ZipCode::class
        );
        foreach ($deletedByZipCode as $code => $entity) {
            if (!in_array($entity, $deletedByJurisdiction)) {
                $updated[$code] = $entity;
            }
        }

        return [
            AbstractExportWriter::CREATE_ACTION => $createdByJurisdiction,
            AbstractExportWriter::UPDATE_ACTION => $updated,
            AbstractExportWriter::DELETE_ACTION => $deletedByJurisdiction
        ];
    }

    /**
     * @param array $entities
     * @param string $class
     * @return array
     */
    private function filterEntities(array $entities, $class)
    {
        $result = [];

        foreach ($entities as $entity) {
            if (is_a($entity, $class)) {
                if ($class === ZipCode::class) {
                    $entity = $entity->getTaxJurisdiction();
                }
                if ($this->isSyncRequired($entity)) {
                    $result[$entity->getCode()] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param TaxJurisdiction|ZipCode $entity
     * @return bool
     */
    protected function isSyncRequired($entity)
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
     * @param TaxJurisdiction $entity
     * @param string $action
     */
    protected function scheduleSync(TaxJurisdiction $entity, $action)
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
                    if (isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]) &&
                        isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId]) &&
                        $data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId] !== null
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
                    if (isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]) &&
                        isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId]) &&
                        $data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                            ProductExportUpdateReader::ID_FILTER =>
                                $data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId],
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
                                    'connector'            => OroCommerceTaxJurisdictionConnector::TYPE,
                                    'transport_batch_size' => 100,
                                ],
                                MessagePriority::NORMAL
                            )
                        );
                    } elseif($settingsBag->get(OroCommerceSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION) === false) {
                        $transportData = $transport->getData();
                        $transportData[AbstractExportWriter::NOT_SYNCHRONIZED]
                        [OroCommerceTaxJurisdictionConnector::TYPE]
                        [$this->generateConnectionParametersKey($connector_params)] = $connector_params;
                        $transport->setData($transportData);
                        $this->entityManager->persist($transport);
                        /** @var OroEventManager $eventManager */
                        $eventManager = $this->entityManager->getEventManager();
                        $eventManager->removeEventListener(
                            'onFlush',
                            'marello_orocommerce.event_listener.doctrine.reverse_sync_tax_jurisdiction'
                        );
                        $this->entityManager->flush($transport);
                    }

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
                'type' => OroCommerceChannelType::TYPE
            ]);

        $integrationChannels = [];
        foreach ($channels as $channel) {
            if ($channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false) &&
                in_array(OroCommerceTaxJurisdictionConnector::TYPE, $channel->getConnectors())) {
                $connectors = $channel->getConnectors();
                if (in_array(OroCommerceTaxJurisdictionConnector::TYPE, $connectors)) {
                    $integrationChannels[] = $channel;
                }
            }
        }

        return $integrationChannels;
    }
}
