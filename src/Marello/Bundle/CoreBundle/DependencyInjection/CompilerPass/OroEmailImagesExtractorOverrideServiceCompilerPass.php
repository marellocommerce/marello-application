<?php

namespace Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass;

use Marello\Bundle\CoreBundle\EmbeddedImages\EmbeddedImagesExtractor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroEmailImagesExtractorOverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('oro_email.embedded_images.extractor')) {
            $definition = $container->getDefinition('oro_email.embedded_images.extractor');
            $definition->setClass(EmbeddedImagesExtractor::class);
        }
    }
}
