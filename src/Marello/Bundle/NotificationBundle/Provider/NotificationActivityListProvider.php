<?php

namespace Marello\Bundle\NotificationBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;

use Marello\Bundle\NotificationBundle\Entity\Notification;

class NotificationActivityListProvider implements ActivityListProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ServiceLink */
    protected $entityManagerLink;

    /** @var ActivityAssociationHelper $activityAssociationHelper */
    protected $activityAssociationHelper;

    /**
     * NotificationActivityListProvider constructor.
     *
     * @param DoctrineHelper            $doctrineHelper
     * @param TranslatorInterface       $translator
     * @param ServiceLink               $entityManagerLink
     * @param ActivityAssociationHelper $activityAssociationHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TranslatorInterface $translator,
        ServiceLink $entityManagerLink,
        ActivityAssociationHelper $activityAssociationHelper
    ) {
        $this->doctrineHelper               = $doctrineHelper;
        $this->translator                   = $translator;
        $this->entityManagerLink            = $entityManagerLink;
        $this->activityAssociationHelper    = $activityAssociationHelper;
    }

    /**
     * Returns true if given target $entityClass is supportes nofitication activity
     * @param string $entityClass
     * @param bool $accessible
     * @return bool
     */
    public function isApplicableTarget($entityClass, $accessible = true)
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            Notification::class,
            $accessible
        );
    }

    /**
     * @param object|Notification $entity
     *
     * @return string
     */
    public function getSubject($entity)
    {
        return $this->translator->trans($entity->getTemplate()->getName(), [], 'MarelloNotification');
    }

    /**
     * @param object|Notification $entity
     *
     * @return string|null
     */
    public function getDescription($entity)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isActivityListApplicable(ActivityList $activityList): bool
    {
        return true;
    }

    /**
     * Get array of ActivityOwners for list entity
     *
     * @param object       $entity
     * @param ActivityList $activityList
     *
     * @return ActivityOwner[]
     */
    public function getActivityOwners($entity, ActivityList $activityList)
    {
        return [];
    }

    /**
     * @param ActivityList $activityListEntity
     *
     * @return array
     */
    public function getData(ActivityList $activityListEntity)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->entityManagerLink->getService();

        /** @var Notification $entity */
        $entity = $em
            ->getRepository($activityListEntity->getRelatedActivityClass())
            ->find($activityListEntity->getRelatedActivityId());

        return [
            'body' => $entity->getBody(),
        ];
    }

    /**
     * @param object $activityEntity
     *
     * @return Organization|null
     */
    public function getOrganization($activityEntity)
    {
        return $activityEntity->getOrganization();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@MarelloNotification/Notification/js/activityItemTemplate.js.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity)
    {
        return [
            'itemView'  => 'marello_notification_thread_view'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner($entity)
    {
        return null;
    }

    /**
     * returns a class name of entity for which we monitor changes
     *
     * @return string
     */
    public function getActivityClass()
    {
        return Notification::class;
    }

    /**
     * returns a class name of entity for which we verify ACL
     *
     * @return string
     */
    public function getAclClass()
    {
        return Notification::class;
    }

    /**
     * @param object $entity
     *
     * @return integer
     */
    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * Check if provider supports given activity
     *
     * @param  object $entity
     *
     * @return bool
     */
    public function isApplicable($entity)
    {
        return $this->doctrineHelper->getEntityClass($entity) === Notification::class;
    }

    /**
     * Returns array of assigned entities for activity
     *
     * @param object $entity
     *
     * @return array
     */
    public function getTargetEntities($entity)
    {
        return $entity->getActivityTargets();
    }
}
