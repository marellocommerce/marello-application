<?php

namespace Marello\Bundle\LocaleBundle;

use Marello\Bundle\LocaleBundle\DependencyInjection\CompilerPass\EntityLocalizationProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarelloLocaleBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EntityLocalizationProvidersCompilerPass());
        parent::build($container);
    }
}
