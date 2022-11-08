<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\TaskBundle\Entity\TaskPriority;
use Oro\Bundle\TaskBundle\Migrations\Data\ORM\LoadTaskPriority;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostAllocationCreateEventListener
{
    public function __construct(
        private TranslatorInterface $translator,
        private ManagerRegistry $registry,
        private RouterInterface $router,
        private ConfigManager $configManager,
        private $tasks = []
    ) {}

    public function postPersist(Allocation $allocation, LifecycleEventArgs $args): void
    {
        $availableStates = $this->configManager->get('marello_inventory.inventory_allocation_states_to_select');
        if (!\in_array($allocation->getState()->getId(), $availableStates)) {
            return;
        }

        $task = new Task();
        $task->setOrganization($allocation->getOrganization());
        $dueDate = new \DateTime('+1 day');
        $dueDate->setTime('23', '59', '59');
        $task->setDueDate($dueDate);
        $task->setSubject(
            $this->translator->trans('marello.inventory.allocation.entity_label')
            . ' ' . $allocation->getState()
        );
        $order = $allocation->getOrder();
        $task->setDescription(implode(PHP_EOL, [
            $this->wrapParagraph($allocation->getState()),
            $this->wrapParagraph($allocation->getStatus()),
            $this->wrapParagraph(PHP_EOL . $this->translator->trans('marello.inventory.allocation.order.label')),
            $this->wrapParagraph(sprintf(
                '<a href="%s" title="%s">%s</a>',
                $this->router->generate('marello_order_order_view', ['id' => $order->getId()]),
                $order->getOrderNumber(),
                $order->getOrderNumber()
            )),
            $this->wrapParagraph($order->getCustomer()->getFullName()),
            $this->wrapParagraph($dueDate->format('Y-m-d H:i:s')),
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
            $allocation = $task->getActivityTargets()[0];
            $description = $task->getDescription();
            // Allocation number part was moved to the postFlush method because an allocation number assigns during
            // the separate postFlush listener. See DerivedPropertySetter class
            $task->setDescription(implode(PHP_EOL, [
                $this->wrapParagraph(sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->router->generate('marello_inventory_allocation_view', ['id' => $allocation->getId()]),
                    $allocation->getAllocationNumber(),
                    $allocation->getAllocationNumber()
                )),
                $description
            ]));

            $args->getEntityManager()->persist($task);
        }
        $this->tasks = [];
        $args->getEntityManager()->flush();
    }

    private function wrapParagraph(string $string): string
    {
        return '<p>' . $string . '</p>';
    }
}
