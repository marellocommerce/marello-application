<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\TaskBundle\Entity\TaskPriority;
use Oro\Bundle\TaskBundle\Migrations\Data\ORM\LoadTaskPriority;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostAllocationCreateEventListener
{
    public function __construct(
        private TranslatorInterface $translator,
        private ManagerRegistry $registry,
        private $tasks = []
    ) {}

    public function postPersist(Allocation $allocation, LifecycleEventArgs $args): void
    {
        $task = new Task();
        $task->setOrganization($allocation->getOrganization());
        $dueDate = new \DateTime('+1 day');
        $dueDate->setTime('23', '59', '59');
        $task->setDueDate($dueDate);
        $task->setSubject(
            $this->translator->trans('marello.inventory.allocation.entity_label')
            . ' ' . $allocation->getState()
        );
        $task->setDescription(implode(PHP_EOL, [
            $allocation->getAllocationNumber(),
            $allocation->getState(),
            $allocation->getStatus(),
            PHP_EOL . $this->translator->trans('marello.inventory.allocation.order.label'),
            $allocation->getOrder()->getOrderNumber(),
            $allocation->getOrder()->getCustomer()->getFullName(),
            $dueDate->format('Y-m-d H:i:s'),
        ]));

        $priorityName = $allocation->getState()->__toString() == AllocationStateStatusInterface::ALLOCATION_STATE_ALERT
            ? LoadTaskPriority::PRIORITY_NAME_HIGH
            : LoadTaskPriority::PRIORITY_NAME_LOW;
        $priority = $this->registry
            ->getRepository(TaskPriority::class)
            ->findOneBy(['name' => $priorityName]);
        $task->setTaskPriority($priority);

        $statusClass = ExtendHelper::buildEnumValueClassName('task_status');
        $statusOpen = $this->registry->getManagerForClass($statusClass)->find($statusClass, 'open');
        $task->setStatus($statusOpen);

        $typeClass = ExtendHelper::buildEnumValueClassName('task_type');
        $typeAllocation = $this->registry->getManagerForClass($typeClass)->find($typeClass, 'allocation');
        $task->setType($typeAllocation);

        $group = $this->registry->getRepository(Group::class)->findOneBy(['name' => 'Administrators']);
        $task->setAssignedToGroup($group);

        $task->addActivityTarget($allocation);
        $this->tasks[] = $task;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->tasks) {
            return;
        }

        foreach ($this->tasks as $task) {
            $args->getEntityManager()->persist($task);
        }
        $this->tasks = [];
        $args->getEntityManager()->flush();
    }
}
