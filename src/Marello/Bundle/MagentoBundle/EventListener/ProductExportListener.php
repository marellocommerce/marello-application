<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 17/05/2018
 * Time: 10:45
 */

namespace Marello\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Marello\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\DependencyInjection\ServiceLink;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ProductExportListener
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

        foreach ($this->getEntitiesToSync() as $entity) {
            $this->scheduleSync($entity);
        }
    }

    /**
     * @return array
     */
    protected function getEntitiesToSync()
    {
        $entities = $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions();
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
                if ($this->isSyncRequired($entity)) {
                    $result[
                    sprintf(
                        '%s',
                        $entity->getSku()
                    )
                    ] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @param Product $entity
     * @return bool
     */
    protected function isSyncRequired(Product $entity)
    {
        $changeSet = $this->entityManager->getUnitOfWork()->getEntityChangeSet($entity);

        if (count($changeSet) === 0) {
            return true;
        }

        return true;
    }

    protected function scheduleSync(Product $entity)
    {
        if (!in_array($entity, $this->processedEntities)) {
            return false;
        }

        $integrationChannels = $this->getIntegrationChannels($entity);

        /** @var Product $product */
        $product = $entity->getProduct();

        $data = $product->getData();

        foreach ($integrationChannels as $integrationChannel) {
            $salesChannel = $this->getSalesChannel($product, $integrationChannel);
            if ($salesChannel) {
                $channelId = $integrationChannel->getId();

                //TODO: schedule the product for export

            }
        }
    }

    /**
     * @param Product $entity
     * @return Channel[]
     */
    protected function getIntegrationChannels(Product $entity)
    {
        $integrationChannels = [];
        $salesChannels = $entity->getChannels();
        foreach ($salesChannels as $salesChannel) {
            $channel = $salesChannel->getIntegrationChannel();
            if ($channel && $channel->getType() === MagentoChannelType::TYPE && $channel->isEnabled() &&
                $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false)
            ) {
                $integrationChannels[] = $channel;
            }
        }

        return $integrationChannels;
    }


    /**
     * @param Product $product
     * @param Channel $integrationChannel
     * @return bool|SalesChannel|mixed
     */
    protected function getSalesChannel(Product $product, Channel $integrationChannel)
    {
        foreach ($product->getChannels() as $salesChannel) {
            if ($salesChannel->getIntegrationChannel() === $integrationChannel) {
                return $salesChannel;
            }
        }

        return false;
    }
}
