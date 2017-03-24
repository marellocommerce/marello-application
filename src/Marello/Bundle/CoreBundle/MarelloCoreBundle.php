<?php

namespace Marello\Bundle\CoreBundle;

use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\OroEmailProcessorOverrideServiceCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigSandboxConfigurationPass());
        $container->addCompilerPass(new OroEmailProcessorOverrideServiceCompilerPass());
        parent::build($container);
    }
}
