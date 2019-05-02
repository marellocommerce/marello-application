<?php

namespace Marello\Bundle\ReturnBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReturnBusinessRuleRegistryCompilerPass implements CompilerPassInterface
{
    const BUSINESS_RULE_TAG   = 'marello_return.manager.rules.businessrule';
    const REGISTRY_SERVICE_ID   = 'marello_return.manager.return_businessrule_registry';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $validationRules  = $container->findTaggedServiceIds(self::BUSINESS_RULE_TAG);

        $registry = $container->findDefinition(self::REGISTRY_SERVICE_ID);

        foreach ($validationRules as $serviceId => $tags) {
            $ref = new Reference($serviceId);
            foreach ($tags as $tag) {
                $registry->addMethodCall('registerBusinessRule', [$tag['alias'], $ref]);
            }
        }
    }
}
