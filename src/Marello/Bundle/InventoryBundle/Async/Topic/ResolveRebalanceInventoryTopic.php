<?php

namespace Marello\Bundle\InventoryBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolveRebalanceInventoryTopic extends AbstractTopic implements JobAwareTopicInterface
{
    public static function getName(): string
    {
        return 'marello_inventory.inventory_rebalance';
    }

    public static function getDescription(): string
    {
        return 'Resolve rebalance inventory';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'product_id',
                'priority',
                'jobId'
            ])
            ->setRequired([
                'product_id',
            ])
            ->addAllowedTypes('product_id', ['int'])
            ->addAllowedTypes('priority', ['string'])
            ->addAllowedTypes('jobId', ['int']);
    }

    public function createJobName($messageBody): string
    {
        return sprintf('%s:%s', self::getName(), $messageBody['product_id']);
    }
}
