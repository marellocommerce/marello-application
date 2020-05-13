<?php

namespace Marello\Bundle\TaxBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TaxRuleMatcherPass implements CompilerPassInterface
{
    const COMPOSITE_SERVICE = 'marello_tax.tax_rule.matcher.composite';
    const TAG = 'marello_tax.tax_rule.matcher';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::COMPOSITE_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        $compiledServiceDefinition = $container->getDefinition(self::COMPOSITE_SERVICE);

        $matchers = [];
        foreach ($taggedServices as $id => $attributes) {
            if (!array_key_exists('priority', $attributes[0])) {
                throw new \InvalidArgumentException(
                    sprintf('Attribute "priority" is missing for "%s" tag at "%s" service', self::TAG, $id)
                );
            }
            $matchers[(int)$attributes[0]['priority']][] = $id;
        }
        ksort($matchers);
        $matchers = call_user_func_array('array_merge', $matchers);

        foreach ($matchers as $matcher) {
            $compiledServiceDefinition->addMethodCall('addMatcher', [new Reference($matcher)]);
        }
    }
}
