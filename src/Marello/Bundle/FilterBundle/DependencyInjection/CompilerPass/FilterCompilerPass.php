<?php

namespace Marello\Bundle\FilterBundle\DependencyInjection\CompilerPass;

use Marello\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilterCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('oro_filter.extension.orm_filter');
        $definition->setClass(OrmFilterExtension::class);
        $definition->addMethodCall('setRequestStack', [new Reference('request_stack')]);
    }
}
