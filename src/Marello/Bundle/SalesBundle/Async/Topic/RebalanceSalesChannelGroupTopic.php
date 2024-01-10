<?php

namespace Marello\Bundle\SalesBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RebalanceSalesChannelGroupTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_sales.rebalance_products_saleschannel_group';
    }

    public static function getDescription(): string
    {
        return 'Rebalance sales channel group';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'salesChannelIds',
            ])
            ->setRequired([
                'salesChannelIds',
            ])
            ->addAllowedTypes('salesChannelIds', ['array']);
    }
}
