<?php

namespace Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwigSandboxConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('oro_email.twig.email_security_policy') &&
            $container->hasDefinition('oro_email.email_renderer')
        ) {
            $securityPolicyDef = $container->getDefinition('oro_email.twig.email_security_policy');

            $functions = $securityPolicyDef->getArgument(4);
            $functions = array_merge(
                $functions,
                ['marello_shipping_method_with_type_label']
            );

            $securityPolicyDef->replaceArgument(4, $functions);

            $rendererDef = $container->getDefinition('oro_email.email_renderer');
            $rendererDef->addMethodCall('addExtension', [
                new Reference('marello_shipping.twig.shipping_method_extension')
            ]);
        }
    }
}
