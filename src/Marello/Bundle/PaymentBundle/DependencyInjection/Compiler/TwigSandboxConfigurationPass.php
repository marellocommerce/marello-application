<?php

namespace Marello\Bundle\PaymentBundle\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\AbstractTwigSandboxConfigurationPass;

/**
 * Compiler pass that collects extensions for service `marello_payment.twig.payment_method_extension` and
 * `marello_payment.twig.payment_status_extension` by `marello_email.email_renderer` tag
 */
class TwigSandboxConfigurationPass extends AbstractTwigSandboxConfigurationPass
{
    /**
     * {@inheritDoc}
     */
    protected function getFunctions(): array
    {
        return [
            'get_payment_methods',
            'get_payment_status_label',
            'get_payment_status'
        ];
    }

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
    protected function getExtensions(): array
    {
        return [
            'marello_payment.twig.payment_method_extension',
            'marello_payment.twig.payment_status_extension'
        ];
    }

    protected function getTags(): array
    {
        return [];
    }
}
