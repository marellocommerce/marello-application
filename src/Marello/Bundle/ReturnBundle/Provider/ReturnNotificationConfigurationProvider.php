<?php

namespace Marello\Bundle\ReturnBundle\Provider;

use Marello\Bundle\NotificationBundle\Provider\EntityNotificationConfigurationProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

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
     * @inheritDoc
     */
    public function isNotificationEnabled($entityClass)
    {
        if (Order::class === $entityClass) {
            return (bool)$this->configManager->get('marello_return.return_notification');
        }

        return false;
    }
}
