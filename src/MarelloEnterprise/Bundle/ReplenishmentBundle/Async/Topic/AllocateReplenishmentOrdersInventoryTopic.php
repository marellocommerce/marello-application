<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Async\Topic;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AllocateReplenishmentOrdersInventoryTopic extends AbstractTopic implements JobAwareTopicInterface
{
    public static function getName(): string
    {
        return 'marelloenterprise_replenishment.allocate_replenishment_orders_inventory';
    }

    public static function getDescription(): string
    {
        return 'Allocate replenishment orders inventory';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                AllocateReplenishmentOrdersInventoryProcessor::ORDERS,
                'jobId'
            ])
            ->setRequired([
                AllocateReplenishmentOrdersInventoryProcessor::ORDERS,
            ])
            ->addAllowedTypes(AllocateReplenishmentOrdersInventoryProcessor::ORDERS, ['array'])
            ->addAllowedTypes('jobId', ['int']);
    }

    public function createJobName($messageBody): string
    {
        return sprintf('%s:%s', self::getName(), $messageBody[AllocateReplenishmentOrdersInventoryProcessor::ORDERS]);
    }
}
