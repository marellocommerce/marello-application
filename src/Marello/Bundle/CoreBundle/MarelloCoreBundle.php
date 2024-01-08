<?php

namespace Marello\Bundle\CoreBundle;

use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\OroEmailImagesExtractorOverrideServiceCompilerPass;
use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\AdditionalPlaceholderProviderPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigSandboxConfigurationPass());
        $container->addCompilerPass(new OroEmailImagesExtractorOverrideServiceCompilerPass());
        $container->addCompilerPass(new AdditionalPlaceholderProviderPass());
        parent::build($container);
    }
}
