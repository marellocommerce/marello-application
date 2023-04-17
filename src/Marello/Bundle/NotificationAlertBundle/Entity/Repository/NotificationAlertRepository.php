<?php

namespace Marello\Bundle\NotificationAlertBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertTypeInterface;

class NotificationAlertRepository extends EntityRepository
{
    public function getOutdatedNotificationAlerts(): array
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
            ->setParameter('yes', NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_YES)
            ->setParameter('infoType', NotificationAlertTypeInterface::NOTIFICATION_ALERT_TYPE_INFO);

        return $qb->getQuery()->getResult();
    }
}
