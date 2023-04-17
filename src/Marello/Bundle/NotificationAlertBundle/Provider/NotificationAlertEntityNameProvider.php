<?php

namespace Marello\Bundle\NotificationAlertBundle\Provider;

use Marello\Bundle\NotificationAlertBundle\Entity\NotificationAlert;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class NotificationAlertEntityNameProvider implements EntityNameProviderInterface
{
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, NotificationAlert::class)) {
            return sprintf('[%s] %s', $entity->getAlertType()->getName(), $entity->getMessage());
        }

        return false;
    }

    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }
}
