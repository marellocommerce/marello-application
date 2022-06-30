<?php

namespace Marello\Bundle\ProductBundle\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

class EmailTwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    const PRODUCT_UNIT_EXTENSION = 'marello_product.twig.product_unit_extension';

    /**
     * {@inheritDoc}
     */
    protected function getFilters(): array
    {
        return [
            'marello_format_product_unit'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctions(): array
    {
        return [
            'get_product_unit_value_by_id'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            self::PRODUCT_UNIT_EXTENSION
        ];
    }

    protected function getTags(): array
    {
        return [];
    }
}
