<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\TaxRuleExportReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRuleExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxRuleConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class ReverseSyncTaxRuleListener extends AbstractReverseSyncListener
{
    const SYNC_FIELDS = [
        'taxCode',
        'taxRate',
        'taxJurisdiction'
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
            if ($entity instanceof TaxRule) {
                if ($this->isSyncRequired($entity)) {
                    $result[
                        sprintf(
                            '%s_%s_%s',
                            $entity->getTaxCode()->getCode(),
                            $entity->getTaxRate()->getCode(),
                            $entity->getTaxJurisdiction()->getCode()
                        )
                    ] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param TaxRule $entity
     * @return bool
     */
    protected function isSyncRequired(TaxRule $entity)
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
     * @param TaxRule $entity
     * @param string $action
     */
    protected function scheduleSync(TaxRule $entity, $action)
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
                        TaxRuleExportReader::TAXCODE_FILTER => $entity->getTaxCode()->getCode(),
                        TaxRuleExportReader::TAXRATE_FILTER => $entity->getTaxRate()->getCode(),
                        TaxRuleExportReader::TAXJURISDICTION_FILTER => $entity->getTaxJurisdiction()->getCode(),
                    ];
                } elseif (AbstractExportWriter::UPDATE_ACTION === $action) {
                    if (isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID]) &&
                        isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId]) &&
                        $data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                            TaxRuleExportReader::TAXCODE_FILTER => $entity->getTaxCode()->getCode(),
                            TaxRuleExportReader::TAXRATE_FILTER => $entity->getTaxRate()->getCode(),
                            TaxRuleExportReader::TAXJURISDICTION_FILTER => $entity->getTaxJurisdiction()->getCode(),
                        ];
                    } else {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                            TaxRuleExportReader::TAXCODE_FILTER => $entity->getTaxCode()->getCode(),
                            TaxRuleExportReader::TAXRATE_FILTER => $entity->getTaxRate()->getCode(),
                            TaxRuleExportReader::TAXJURISDICTION_FILTER => $entity->getTaxJurisdiction()->getCode(),
                        ];
                    }
                } elseif (AbstractExportWriter::DELETE_ACTION === $action) {
                    if (isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID]) &&
                        isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId]) &&
                        $data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                            ProductExportUpdateReader::ID_FILTER =>
                                $data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId],
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
                                    'connector'            => OroCommerceTaxRuleConnector::TYPE,
                                    'transport_batch_size' => 100,
                                ],
                                MessagePriority::NORMAL
                            )
                        );
                    } elseif($settingsBag->get(OroCommerceSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION) === false) {
                        $transportData = $transport->getData();
                        $transportData[AbstractExportWriter::NOT_SYNCHRONIZED]
                        [OroCommerceTaxRuleConnector::TYPE]
                        [$this->generateConnectionParametersKey($connector_params)] = $connector_params;
                        $transport->setData($transportData);
                        $this->entityManager->persist($transport);
                        /** @var OroEventManager $eventManager */
                        $eventManager = $this->entityManager->getEventManager();
                        $eventManager->removeEventListener(
                            'onFlush',
                            'marello_orocommerce.event_listener.doctrine.reverse_sync_tax_rule'
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
                in_array(OroCommerceTaxRuleConnector::TYPE, $channel->getConnectors())) {
                $connectors = $channel->getConnectors();
                if (in_array(OroCommerceTaxRuleConnector::TYPE, $connectors)) {
                    $integrationChannels[] = $channel;
                }
            }
        }

        return $integrationChannels;
    }
}
