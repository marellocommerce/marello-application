<?php

namespace Marello\Bundle\NotificationMessageBundle\Provider;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Component\DependencyInjection\ServiceLink;
use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;

class NotificationMessageActivityListProvider implements ActivityListProviderInterface, ActivityListDateProviderInterface
{
    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected ActivityAssociationHelper $activityAssociationHelper,
        protected ServiceLink $entityOwnerAccessorLink
    ) {}

    /**
     * @param string $entityClass
     * @param bool $accessible
     * @return bool
     */
    public function isApplicableTarget($entityClass, $accessible = true)
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            NotificationMessage::class,
            $accessible
        );
    }

    /**
     * @param object|NotificationMessage $entity
     *
     * @return string
     */
    public function getSubject($entity)
    {
        return $entity->getTitle();
    }

    /**
     * @param object|NotificationMessage $entity
     *
     * @return string|null
     */
    public function getDescription($entity)
    {
        return $entity->getMessage();
    }

    /**
     * @param object|NotificationMessage $entity
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
        $repo = $this->doctrineHelper->getEntityRepositoryForClass($activityListEntity->getRelatedActivityClass());

        /** @var NotificationMessage $entity */
        $entity = $repo->find($activityListEntity->getRelatedActivityId());

        return [
            'body' => $entity->getTitle(),
            'alertType' => $entity->getAlertType()->getId(),
            'alertTypeName' => $entity->getAlertType()->getName(),
        ];
    }

    /**
     * @param NotificationMessage $activityEntity
     *
     * @return Organization|OrganizationInterface|null
     */
    public function getOrganization($activityEntity)
    {
        return $activityEntity->getOrganization();
    }

    public function getCreatedAt($entity)
    {
        /** @var $entity NotificationMessage */
        return $entity->getCreatedAt();
    }

    public function getUpdatedAt($entity)
    {
        /** @var $entity NotificationMessage */
        return $entity->getUpdatedAt();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return '@MarelloNotificationMessage/NotificationMessage/js/activityItemTemplate.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($activityEntity)
    {
        return [
            'itemView'  => 'marello_notificationmessage_widget_info'
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
     * @param object $entity
     *
     * @return integer
     */
    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isApplicable($entity)
    {
        return $this->doctrineHelper->getEntityClass($entity) === NotificationMessage::class;
    }

    /**
     * @param object|NotificationMessage $entity
     *
     * @return array
     */
    public function getTargetEntities($entity)
    {
        return $entity->getActivityTargets();
    }
}
