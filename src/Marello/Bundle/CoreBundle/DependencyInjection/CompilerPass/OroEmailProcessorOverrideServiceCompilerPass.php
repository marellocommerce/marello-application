<?php

namespace Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\CoreBundle\Mailer\Processor;

class OroEmailProcessorOverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('oro_email.mailer.processor')) {
            $definition = $container->getDefinition('oro_email.mailer.processor');
            $definition->setClass(Processor::class);
        }
    }
}
