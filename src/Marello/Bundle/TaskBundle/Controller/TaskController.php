<?php

namespace Marello\Bundle\TaskBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use Oro\Bundle\TaskBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @Route(
     *     "/widget/sidebar-allocation-tasks/{perPage}",
     *     name="marello_task_widget_sidebar_allocation_tasks",
     *     defaults={"perPage" = 10},
     *     requirements={"perPage"="\d+"}
     * )
     * @AclAncestor("oro_task_view")
     */
    public function tasksWidgetAction(int $perPage): Response
    {
        /** @var TaskRepository $taskRepository */
        $taskRepository = $this->container->get('doctrine')->getRepository(Task::class);
        $userId = $this->getUser()->getId();
        $tasks = $taskRepository->getAllocationTasksAssignedTo($userId, $perPage);

        return $this->render('@MarelloTask/Task/widget/allocationTasksWidget.html.twig', ['tasks' => $tasks]);
    }
}
