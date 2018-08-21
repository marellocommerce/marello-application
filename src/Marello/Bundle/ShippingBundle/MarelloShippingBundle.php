<?php

namespace Marello\Bundle\ShippingBundle;

use Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\ShippingMethodsCompilerPass;
use Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\ShippingServiceRegistryCompilerPass;
use Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloShippingBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ShippingServiceRegistryCompilerPass());
        $container->addCompilerPass(new ShippingMethodsCompilerPass());
        $container->addCompilerPass(new TwigSandboxConfigurationPass());
    }
}
