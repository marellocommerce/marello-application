<?php

namespace Marello\Bundle\TaskBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TaskBundle\Entity\Task;

class DefaultTaskTypeListener
{
    public function __construct(
        private ManagerRegistry $registry
    ) {}

    public function prePersist(Task $task)
    {
        if ($task->getType()) {
            return;
        }

        $typeClass = ExtendHelper::buildEnumValueClassName('task_type');
        $typeAllocation = $this->registry->getManagerForClass($typeClass)->find($typeClass, 'general');
        $task->setType($typeAllocation);
    }
}
