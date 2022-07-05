<?php

namespace Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass;

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

            $filters = $securityPolicyDef->getArgument(1);
            $filters = array_merge(
                $filters,
                [
                    'oro_format_currency'
                ]
            );

            $securityPolicyDef->replaceArgument(1, $filters);

            $rendererDef = $container->getDefinition('oro_email.email_renderer');
            $rendererDef->addMethodCall('addExtension', [new Reference('oro_locale.twig.number')]);
        }
    }
}
