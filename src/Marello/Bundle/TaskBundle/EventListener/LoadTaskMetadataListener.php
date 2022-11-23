<?php

namespace Marello\Bundle\TaskBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Marello\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use Oro\Bundle\TaskBundle\Entity\Task;

class LoadTaskMetadataListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->getName() !== Task::class) {
            return;
        }

        $classMetadata->customRepositoryClassName = TaskRepository::class;
    }
}
