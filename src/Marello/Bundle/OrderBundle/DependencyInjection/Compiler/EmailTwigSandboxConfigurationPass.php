<?php

namespace Marello\Bundle\OrderBundle\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

class EmailTwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    const ORDER_EXTENSION = 'marello_order.twig.order_extension';

    /**
     * {@inheritDoc}
     */
    protected function getFilters()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctions()
    {
        return [
            'marello_get_order_item_status',
            'marello_get_order_items_for_notification'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
    {
        return [
            self::ORDER_EXTENSION
        ];
    }
}
