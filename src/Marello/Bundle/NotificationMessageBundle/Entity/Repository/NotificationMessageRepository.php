<?php

namespace Marello\Bundle\NotificationMessageBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Oro\Bundle\UserBundle\Entity\User;
use function Doctrine\ORM\QueryBuilder;

class NotificationMessageRepository extends EntityRepository
{
    public function getOutdatedNotificationMessages(): array
    {
        $monthAgo = (new \DateTime())->modify('-30 days');
        $qb = $this->createQueryBuilder('na');
        $qb
            ->where($qb->expr()->lt('na.createdAt', ':monthAgo'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('na.resolved', ':yes'),
                $qb->expr()->eq('na.alertType', ':infoType')
            ))
            ->setParameter('monthAgo', $monthAgo)
            ->setParameter('yes', NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES)
            ->setParameter('infoType', NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO);

        return $qb->getQuery()->getResult();
    }

    public function getNotificationMessagesAssignedTo(User $user, int $limit, array $types)
    {
        $queryBuilder = $this->createQueryBuilder('na');

        $queryBuilder
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in('na.userGroup', ':groups'),
                    $queryBuilder->expr()->isNull('na.userGroup'),
                )
            )
            ->orderBy('na.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->setParameter('groups', $user->getGroups()->toArray());

        if ($types) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('na.alertType', ':types'))
                ->setParameter('types', $types);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
