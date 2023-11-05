<?php

namespace Marello\Bundle\NotificationMessageBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageTypeInterface;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;

class NotificationMessageRepository extends ServiceEntityRepository
{
    /** @var AclHelper $aclHelper */
    private AclHelper $aclHelper;

    /**
     * @return array
     */
    public function getOutdatedNotificationMessages(): array
    {
        $monthAgo = (new \DateTime())->modify('-30 days');
        $qb = $this->createQueryBuilder('na');
        $qb
            ->where($qb->expr()->lt('na.createdAt', ':monthAgo'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('na.resolved', ':resolved'),
                $qb->expr()->eq('na.alertType', ':infoType')
            ))
            ->setParameter('monthAgo', $monthAgo)
            ->setParameter('resolved', NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES)
            ->setParameter('infoType', NotificationMessageTypeInterface::NOTIFICATION_MESSAGE_TYPE_INFO);

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @param User $user
     * @param int $limit
     * @param array $types
     * @return float|int|mixed|string
     */
    public function getNotificationMessagesAssignedTo(User $user, int $limit, array $types)
    {
        $qb = $this->createQueryBuilder('na');

        $qb
            ->where($qb->expr()->neq('na.resolved', ':resolved'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('na.userGroup', ':groups'),
                    $qb->expr()->isNull('na.userGroup'),
                )
            )
            ->orderBy('na.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->setParameter('groups', $user->getGroups()->toArray())
            ->setParameter(
                'resolved',
                NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_YES
            );

        if ($types) {
            $qb->andWhere($qb->expr()->in('na.alertType', ':types'))
                ->setParameter('types', $types);
        }

        return $this->aclHelper->apply($qb->getQuery())->execute();
    }

    /**
     * @param AclHelper $aclHelper
     * @return void
     */
    public function setAclHelper(AclHelper $aclHelper): void
    {
        $this->aclHelper = $aclHelper;
    }
}
