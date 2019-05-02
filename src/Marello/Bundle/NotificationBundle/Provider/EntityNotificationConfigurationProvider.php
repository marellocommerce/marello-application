<?php

namespace Marello\Bundle\NotificationBundle\Provider;

class EntityNotificationConfigurationProvider implements EntityNotificationConfigurationProviderInterface
{
    /**
     * @var EntityNotificationConfigurationProviderInterface[]
     */
    private $providers = [];

    /**
     * @param string $entityClass
     * @param EntityNotificationConfigurationProviderInterface $provider
     * @return $this
     */
    public function addProvider($entityClass, EntityNotificationConfigurationProviderInterface $provider)
    {
        $this->providers[$entityClass] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotificationEnabled($entityClass)
    {
        if (isset($this->providers[$entityClass])) {
            return $this->providers[$entityClass]->isNotificationEnabled($entityClass);
        }
        
        return true;
    }
}
