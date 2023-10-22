<?php

namespace Marello\Bundle\WebhookBundle\Event;

class BalancedInventoryUpdateAfterWebhookEvent extends AbstractWebhookEvent
{
    public static function getName(): string
    {
        return 'marello_inventory.balancedinventory.update_after';
    }

    public static function getLabel(): string
    {
        return 'Balanced Inventory Update After';
    }

    protected function getContextData(): array
    {
        return [];
    }
}
