<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;

/**
 * Class StockLevelSubjectHydrationSubscriber
 *
 * Hydrates the subject field of InventoryLevel entity.
 * Subject is stored as class name and id. A reference to this entity is created so it can be accessed.
 *
 * @package Marello\Bundle\InventoryBundle\EventListener\Doctrine
 */
class StockLevelSubjectHydrationSubscriber implements EventSubscriber
{
    use SetsPropertyValue;

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof InventoryLevelLogRecord) {
            return;
        }

        /*
         * Guard against InventoryLevels without subject stored.
         */
        if (!$entity->getSubjectType() || !$entity->getSubjectId()) {
            return;
        }

        /*
         * Create reference to subject entity.
         * This does not guarantee that this entity instance exists.
         */
        $subject = $args->getObjectManager()
            ->getReference(
                $entity->getSubjectType(),
                $entity->getSubjectId()
            );

        /*
         * Set new subject value.
         */
        $this->setPropertyValue($entity, 'subject', $subject);
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
        ];
    }
}
