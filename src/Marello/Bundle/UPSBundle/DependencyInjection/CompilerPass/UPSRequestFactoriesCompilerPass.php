<?php

namespace Marello\Bundle\UPSBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class UPSRequestFactoriesCompilerPass implements CompilerPassInterface
{
    const TAG = 'marello_ups_request_factory';
    const SERVICE = 'marello_ups.request_factory';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(static::TAG);
        if (!$taggedServices) {
            return;
        }

        $definition = $container->getDefinition(static::SERVICE);
        foreach ($taggedServices as $factory => $tags) {
            foreach ($tags as $tag) {
                if (!array_key_exists('request_class', $tag)) {
                    throw new \Exception(
                        'Parameter "request_class" is mandatory for "marello_ups_request_factory" tag'
                    );
                }
                $definition->addMethodCall('addFactory', [new Reference($factory), $tag['request_class']]);
            }
        }
    }
}
