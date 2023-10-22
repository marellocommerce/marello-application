<?php

namespace Marello\Bundle\InventoryBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolveRebalanceAllInventoryTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_inventory.inventory_rebalance_all';
    }

    public static function getDescription(): string
    {
        return 'Resolve rebalance all inventory';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
