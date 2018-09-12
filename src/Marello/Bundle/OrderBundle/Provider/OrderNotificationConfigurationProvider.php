<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\NotificationBundle\Provider\EntityNotificationConfigurationProviderInterface;

class OrderNotificationConfigurationProvider implements EntityNotificationConfigurationProviderInterface
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
        if (Order::class === $entityClass) {
            return (bool)$this->configManager->get('marello_order.order_notification');
        }

        return false;
    }
}
