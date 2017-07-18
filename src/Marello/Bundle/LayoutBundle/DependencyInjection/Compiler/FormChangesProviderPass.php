<?php

namespace Marello\Bundle\LayoutBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormChangesProviderPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_layout.provider.form_changes_data.composite';
    const TAG = 'marello.form_changes_data_provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::COMPOSITE_SERVICE)) {
            return;
        }

        $services = $container->findTaggedServiceIds(self::TAG);
        if (empty($services)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::COMPOSITE_SERVICE);

        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!array_key_exists('class', $attributes)) {
                    throw new \InvalidArgumentException(
                        sprintf('Attribute "class" is missing for "%s" tag at "%s" service', self::TAG, $id)
                    );
                }
                if (!array_key_exists('type', $attributes)) {
                    throw new \InvalidArgumentException(
                        sprintf('Attribute "type" is missing for "%s" tag at "%s" service', self::TAG, $id)
                    );
                }

                $reference = new Reference($id);
                $registryDefinition->addMethodCall(
                    'addProvider',
                    [
                        $reference,
                        $attributes['class'],
                        $attributes['type']
                    ]);
            }
        }
    }
}
