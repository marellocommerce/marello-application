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
    protected function getFunctions()
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
    protected function getFilters()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions()
    {
        return [
            'marello_payment.twig.payment_method_extension',
            'marello_payment.twig.payment_status_extension'
        ];
    }
}
