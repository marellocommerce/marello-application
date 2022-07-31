<?php

namespace Marello\Bundle\TaskBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\OrganizationBundle\EventListener\RecordOwnerDataListener;
use Oro\Bundle\TaskBundle\Entity\Task;

class RecordOwnerDataListenerDecorator
{
    public function __construct(
        private RecordOwnerDataListener $innerListener
    ) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        if ($args->getEntity() instanceof Task) {
            return;
        }

        $this->innerListener->prePersist($args);
    }
}
