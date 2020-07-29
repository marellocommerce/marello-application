<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\Magento2Bundle\Entity\Magento2Transport;
use Oro\Bundle\IntegrationBundle\Manager\GenuineSyncScheduler;

class Magento2TransportListener
{
    /** @var GenuineSyncScheduler */
    protected $genuineSyncScheduler;

    /** @var array */
    protected $trackedTransportPropertyNames = [];

    /** @var array */
    protected $integrationIdsOnSync = [];

    /**
     * @param GenuineSyncScheduler $genuineSyncScheduler
     * @param array $trackedTransportPropertyNames
     */
    public function __construct(
        GenuineSyncScheduler $genuineSyncScheduler,
        array $trackedTransportPropertyNames
    ) {
        $this->genuineSyncScheduler = $genuineSyncScheduler;
        $this->trackedTransportPropertyNames = $trackedTransportPropertyNames;
    }

    /**
     * @param Magento2Transport $transport
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Magento2Transport $transport, LifecycleEventArgs $args)
    {
        if (false === $transport->getChannel()->isEnabled()) {
            return;
        }

        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($transport);
        $isTrackablePropertiesyChanged = [] !== \array_intersect(
            $this->trackedTransportPropertyNames,
            \array_keys($changeSet)
        );

        if (!$isTrackablePropertiesyChanged) {
            return;
        }

        $this->integrationIdsOnSync[$transport->getChannel()->getId()] = $transport->getChannel()->getId();
    }

    public function postFlush()
    {
        foreach ($this->integrationIdsOnSync as $integrationId) {
            $this->genuineSyncScheduler->schedule($integrationId);
        }

        $this->integrationIdsOnSync = [];
    }

    public function onClear()
    {
        $this->integrationIdsOnSync = [];
    }
}
