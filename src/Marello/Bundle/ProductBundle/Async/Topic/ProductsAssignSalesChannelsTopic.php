<?php

namespace Marello\Bundle\ProductBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductsAssignSalesChannelsTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'marello_product.assign_sales_channels_to_products';
    }

    public static function getDescription(): string
    {
        return 'Assign sales channels to products';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'products',
                'salesChannels',
                'jobId',
            ])
            ->setRequired([
                'products',
                'salesChannels',
            ])
            ->addAllowedTypes('products', ['array'])
            ->addAllowedTypes('salesChannels', ['array'])
            ->addAllowedTypes('jobId', ['int']);
    }
}
