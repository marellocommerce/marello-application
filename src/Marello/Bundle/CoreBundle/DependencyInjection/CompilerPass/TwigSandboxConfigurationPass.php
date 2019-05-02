<?php

namespace Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass;

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

            $filters = $securityPolicyDef->getArgument(1);
            $filters = array_merge(
                $filters,
                [
                    'oro_format_currency'
                ]
            );

            $securityPolicyDef->replaceArgument(1, $filters);

            $rendererDef = $container->getDefinition(BaseTwigSandboxPass::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY);
            $rendererDef->addMethodCall('addExtension', [new Reference('oro_locale.twig.number')]);
        }
    }
}
