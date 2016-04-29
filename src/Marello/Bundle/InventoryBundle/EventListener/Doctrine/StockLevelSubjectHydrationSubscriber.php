<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;

class StockLevelSubjectHydrationSubscriber implements EventSubscriber
{
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof StockLevel) {
            return;
        }
        
        if (!$entity->getSubjectType() || !$entity->getSubjectId()) {
            return;
        }

        $subject = $args->getEntityManager()
            ->getReference(
                $entity->getSubjectType(),
                $entity->getSubjectId()
            );
        
        $entity->setSubject($subject);
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
