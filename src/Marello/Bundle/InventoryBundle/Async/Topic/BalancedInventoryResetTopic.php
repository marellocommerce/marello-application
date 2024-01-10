<?php

namespace Marello\Bundle\InventoryBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BalancedInventoryResetTopic extends AbstractTopic implements JobAwareTopicInterface
{
    public static function getName(): string
    {
        return 'marello_inventory.balancedinventory.reset';
    }

    public static function getDescription(): string
    {
        return 'Reset balanced inventory';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'blncd_inventory_level_id',
                'jobId',
            ])
            ->setRequired([
                'blncd_inventory_level_id',
            ])
            ->addAllowedTypes('blncd_inventory_level_id', ['int'])
            ->addAllowedTypes('jobId', ['int']);
    }

    public function createJobName($messageBody): string
    {
        return sprintf('%s:%s', self::getName(), $messageBody['blncd_inventory_level_id']);
    }
}
