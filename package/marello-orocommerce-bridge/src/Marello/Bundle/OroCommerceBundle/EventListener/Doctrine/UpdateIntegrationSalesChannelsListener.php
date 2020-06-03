<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;

class UpdateIntegrationSalesChannelsListener
{
    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var EntityManager
     */
    protected $em;


    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->unitOfWork = $this->em->getUnitOfWork();

        if (!empty($this->unitOfWork->getScheduledEntityUpdates())) {
            $records = $this->filterRecords($this->unitOfWork->getScheduledEntityUpdates());
            $this->applyCallBackForChangeSet('updateIntegrationChannelsToSalesChannelsFromGroup', $records);
        }
    }

    /**
     * @param array $records
     * @return array
     */
    protected function filterRecords(array $records)
    {
        return array_filter($records, [$this, 'getIsEntityInstanceOf']);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function getIsEntityInstanceOf($entity)
    {
        return ($entity instanceof SalesChannel);
    }

    /**
     * @param string $callback function
     * @param array $changeSet
     * @throws \Exception
     */
    protected function applyCallBackForChangeSet($callback, array $changeSet)
    {
        try {
            array_walk($changeSet, [$this, $callback]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     * @param SalesChannel $entity
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function updateIntegrationChannelsToSalesChannelsFromGroup(SalesChannel $entity)
    {
        $salesChannelGroup = $entity->getGroup();
        $integrationChannel = $this->getIntegrationChannel($salesChannelGroup);
        if ($integrationChannel) {
            $entity->setIntegrationChannel($integrationChannel);
        } else {
            $entity->setIntegrationChannel(null);
        }
        $this->em->persist($entity);
        $this->unitOfWork->scheduleForUpdate($entity);
    }

    /**
     * @param SalesChannelGroup $entity
     * @return null|Channel
     */
    private function getIntegrationChannel(SalesChannelGroup $salesChannelGroup)
    {
        /** @var OroCommerceSettings $transport */
        $transport = $this->em
            ->getRepository(OroCommerceSettings::class)
            ->findOneBy(['salesChannelGroup' => $salesChannelGroup]);
        if ($transport) {
            return $transport->getChannel();
        }

        return null;
    }
}
