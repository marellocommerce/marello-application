<?php

namespace Marello\Bundle\ReturnBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\NotificationBundle\Provider\EntityNotificationConfigurationProviderInterface;

class ReturnNotificationConfigurationProvider implements EntityNotificationConfigurationProviderInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotificationEnabled($entityClass)
    {
        if (ReturnEntity::class === $entityClass) {
            return (bool)$this->configManager->get('marello_return.return_notification');
        }

        return false;
    }
}
