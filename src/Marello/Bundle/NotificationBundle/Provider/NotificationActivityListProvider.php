<?php

namespace Marello\Bundle\NotificationBundle\Provider;

use Marello\Bundle\NotificationBundle\Entity\Notification;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class NotificationActivityListProvider implements ActivityListProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * NotificationActivityListProvider constructor.
     *
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Returns true if given target $configId is supported by activity
     *
     * @param ConfigIdInterface $configId
     * @param ConfigManager     $configManager
     *
     * @return bool
     */
    public function isApplicableTarget(ConfigIdInterface $configId, ConfigManager $configManager)
    {
        $provider = $configManager->getProvider('activity');

        return $provider->hasConfigById($configId)
            && $provider->getConfigById($configId)->has('activities')
            && in_array(Notification::class, $provider->getConfigById($configId)->get('activities'));
    }

    /**
     * @param object|Notification $entity
     *
     * @return string
     */
    public function getSubject($entity)
    {
        return $entity->getTemplate()->getSubject();
    }

    /**
     * @param object $entity
     *
     * @return string|null
     */
    public function getDescription($entity)
    {
        return null;
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
        return [];
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
        return 'MarelloNotificationBundle:Notification/js:activityItemTemplate.js.twig';
    }

    /**
     * Should return array of route names as key => value
     * e.g. [
     *      'itemView'  => 'item_view_route',
     *      'itemEdit'  => 'item_edit_route',
     *      'itemDelete => 'item_delete_route'
     * ]
     *
     * @return array
     */
    public function getRoutes()
    {
        return [];
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
        return $entity->getActivityTargetEntities();
    }
}
