<?php

namespace Marello\Bundle\ProductBundle\Async\Topic;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Component\MessageQueue\Topic\AbstractTopic;

class ProductImageUpdateTopic extends AbstractTopic
{
    /**
     * {@inheritDoc}
     * @return string
     */
    public static function getName(): string
    {
        return 'marello_product.product_image_update';
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Update product images with media url';
    }

    /**
     * {@inheritDoc}
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'productId',
            ])
            ->setRequired([
                'productId',
            ])
            ->addAllowedTypes('productId', ['int']);
    }
}
