<?php

namespace Marello\Bridge\MarelloOroCommerce\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DuplicatedServicesCompilerPass implements CompilerPassInterface
{
    const CHANNEL_SERVICE = 'marello_ups.provider.channel';
    const TRANSPORT_SERVICE = 'marello_ups.provider.transport';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $channelDefinition = $container->getDefinition(self::CHANNEL_SERVICE);
        $channelDefinition->setTags([]);
        $transportDefinition = $container->getDefinition(self::TRANSPORT_SERVICE);
        $transportDefinition->setTags([]);
    }
}
