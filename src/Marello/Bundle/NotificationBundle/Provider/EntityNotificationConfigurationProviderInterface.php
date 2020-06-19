<?php

namespace Marello\Bundle\NotificationBundle\Provider;

interface EntityNotificationConfigurationProviderInterface
{
    /**
     * @param string $entityClass
     * @return bool
     */
    public function isNotificationEnabled($entityClass);
}
