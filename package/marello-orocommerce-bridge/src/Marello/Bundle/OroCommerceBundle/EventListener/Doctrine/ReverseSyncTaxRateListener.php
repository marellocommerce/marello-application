<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\TaxExportReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRateExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxRateConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\DependencyInjection\ServiceLink;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReverseSyncTaxRateListener
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
     * @var array
     */
    private $processedEntities = [];

    /**
     * @var array
     */
    protected $syncFields = [
        'code',
        'description',
        'rate'
    ];

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
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->entityManager = $event->getEntityManager();

        // check for logged user is for confidence that data changes mes from UI, not from sync process.
        if (!$this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->getUser()) {
            return;
        }

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
            if ($entity instanceof TaxRate) {
                if ($this->isSyncRequired($entity)) {
                    $result[$entity->getCode()] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param TaxRate $entity
     * @return bool
     */
    protected function isSyncRequired(TaxRate $entity)
    {
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        if (count($changeSet) === 0) {
            return true;
        }
        foreach (array_keys($changeSet) as $fieldName) {
            if (in_array($fieldName, $this->syncFields)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TaxRate $entity
     * @param string $action
     */
    protected function scheduleSync(TaxRate $entity, $action)
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
                    if (isset($data[TaxRateExportCreateWriter::TAX_ID]) &&
                        isset($data[TaxRateExportCreateWriter::TAX_ID][$channelId]) &&
                        $data[TaxRateExportCreateWriter::TAX_ID][$channelId] !== null
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
                    if (isset($data[TaxRateExportCreateWriter::TAX_ID]) &&
                        isset($data[TaxRateExportCreateWriter::TAX_ID][$channelId]) &&
                        $data[TaxRateExportCreateWriter::TAX_ID][$channelId] !== null
                    ) {
                        $connector_params = [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::DELETE_ACTION,
                            ProductExportUpdateReader::ID_FILTER =>
                                $data[TaxRateExportCreateWriter::TAX_ID][$channelId],
                        ];
                    }
                }

                if (!empty($connector_params)) {
                    $this->syncScheduler->getService()->schedule(
                        $integrationChannel->getId(),
                        OroCommerceTaxRateConnector::TYPE,
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
                in_array(OroCommerceTaxRateConnector::TYPE, $channel->getConnectors())) {
                $integrationChannels[] = $channel;
            }
        }

        return $integrationChannels;
    }
}
