<?php

namespace Marello\Bundle\TaskBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\TaskBundle\Entity\Repository\TaskRepository as BaseTaskRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

class TaskRepository extends BaseTaskRepository
{
    private const CLOSED_STATE = 'closed';

    public function getTasksAssignedTo($userId, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('task');
        $this->joinWorkflowStep($queryBuilder);

        return $queryBuilder
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('task.owner', ':assignedTo'),
                    $queryBuilder->expr()->neq('workflowStep.name', ':step'),
                    $queryBuilder->expr()->eq('task.type', ':type'),
                )
            )
            ->orderBy('task.dueDate', 'ASC')
            ->addOrderBy('workflowStep.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->setParameter('assignedTo', $userId)
            ->setParameter('step', self::CLOSED_STATE)
            ->setParameter('type', 'general')
            ->getQuery()
            ->execute();
    }

    public function getAllocationTasksAssignedTo($userId, $limit)
    {
        $user = $this->_em->getRepository(User::class)->find($userId);
        $queryBuilder = $this->createQueryBuilder('task');
        $this->joinWorkflowStep($queryBuilder);

        return $queryBuilder
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('task.assignedToUser', ':assignedToUser'),
                    $queryBuilder->expr()->in('task.assignedToGroup', ':assignedToGroup'),
                ),
                $queryBuilder->expr()->eq('task.type', ':type'),
                $queryBuilder->expr()->neq('workflowStep.name', ':step')
            )
            ->orderBy('task.dueDate', 'ASC')
            ->addOrderBy('workflowStep.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->setParameter('assignedToUser', $userId)
            ->setParameter('assignedToGroup', $user->getGroups()->toArray())
            ->setParameter('type', 'allocation')
            ->setParameter('step', self::CLOSED_STATE)
            ->getQuery()
            ->execute();
    }

    public function getTaskListByTimeIntervalQueryBuilder($userId, $startDate, $endDate, $extraFields = [])
    {
        $user = $this->_em->getRepository(User::class)->find($userId);
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('t.id, t.subject, t.description, t.dueDate, t.createdAt, t.updatedAt')
            ->where($qb->expr()->andX(
                $qb->expr()->orX(
                    $qb->expr()->eq('t.assignedToUser', ':assignedToUser'),
                    $qb->expr()->in('t.assignedToGroup', ':assignedToGroup'),
                ),
                $qb->expr()->eq('t.type', ':type'),
                $qb->expr()->gte('t.dueDate', ':start'),
                $qb->expr()->lte('t.dueDate', ':end'),
            ))
            ->setParameter('assignedToUser', $userId)
            ->setParameter('assignedToGroup', $user->getGroups()->toArray())
            ->setParameter('type', 'allocation')
            ->setParameter('start', $startDate, Types::DATETIME_MUTABLE)
            ->setParameter('end', $endDate, Types::DATETIME_MUTABLE);
        if ($extraFields) {
            foreach ($extraFields as $field) {
                $qb->addSelect(QueryBuilderUtil::getField('t', $field));
            }
        }

        return $qb;
    }
}
