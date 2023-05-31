<?php

namespace Marello\Bundle\NotificationMessageBundle\Provider;

use Marello\Bundle\NotificationMessageBundle\Entity\NotificationMessage;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class NotificationMessageEntityNameProvider implements EntityNameProviderInterface
{
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, NotificationMessage::class)) {
            return sprintf('[%s] %s', $entity->getAlertType()->getName(), $entity->getTitle());
        }

        return false;
    }

    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }
}
