<?php

namespace Marello\Bundle\OrderBundle\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

class EmailTwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    const ORDER_EXTENSION = 'marello_order.twig.order_extension';

    /**
     * {@inheritDoc}
     */
    protected function getFilters(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctions(): array
    {
        return [
            'marello_get_order_item_status',
            'marello_get_order_items_for_notification'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            self::ORDER_EXTENSION
        ];
    }

    protected function getTags(): array
    {
        return [];
    }
}
