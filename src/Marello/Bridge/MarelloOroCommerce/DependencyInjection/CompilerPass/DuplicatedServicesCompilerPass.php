<?php

namespace Marello\Bridge\MarelloOroCommerce\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DuplicatedServicesCompilerPass implements CompilerPassInterface
{
    const DUPLICATED_SERVICES = [
        'marello_ups.provider.channel',
        'marello_ups.provider.transport',
        'marello_payment_term.integration.channel',
        'marello_payment_term.integration.transport'
    ];

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach (self::DUPLICATED_SERVICES as $service) {
            $definition = $container->getDefinition($service);
            $definition->setTags([]);
        }
    }
}
