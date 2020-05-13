<?php

namespace Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass as BaseTwigSandboxPass;

class TwigSandboxConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(BaseTwigSandboxPass::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY) &&
            $container->hasDefinition(BaseTwigSandboxPass::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY)
        ) {
            $securityPolicyDef = $container->getDefinition(
                BaseTwigSandboxPass::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY
            );

            $functions = $securityPolicyDef->getArgument(4);
            $functions = array_merge(
                $functions,
                ['marello_shipping_method_with_type_label']
            );

            $securityPolicyDef->replaceArgument(4, $functions);

            $rendererDef = $container->getDefinition(BaseTwigSandboxPass::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY);
            $rendererDef->addMethodCall('addExtension', [
                new Reference('marello_shipping.twig.shipping_method_extension')
            ]);
        }
    }
}
