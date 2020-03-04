<?php

namespace Marello\Bundle\PdfBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RenderParameterProviderPass implements CompilerPassInterface
{
    const TAG_NAME = 'marello_pdf.render_parameter_provider';
    const PROVIDER_SERVICE_ID = 'marello_pdf.provider.render_parameters';

    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            $definition = $container->getDefinition(self::PROVIDER_SERVICE_ID);

            $services = $container->findTaggedServiceIds(self::TAG_NAME);
            foreach ($services as $serviceId => $tags) {
                $definition->addMethodCall('addProvider', [new Reference($serviceId)]);
            }
        }
    }
}
