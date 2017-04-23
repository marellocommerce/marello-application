<?php

namespace Marello\Bundle\ExtendWorkflowBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Marello\Bundle\ExtendWorkflowBundle\Extension\StartTransitionButtonProviderExtension;
use Marello\Bundle\ExtendWorkflowBundle\Extension\TransitionButtonProviderExtension;

class TransitionButtonProviderExtensionOverridePass implements CompilerPassInterface
{
    const SERVICE_DEFINITION_START_BUTTON_PROVIDER = 'oro_workflow.extension.start_transition_button_provider';
    const SERVICE_DEFINITION_BUTTON_PROVIDER = 'oro_workflow.extension.transition_button_provider';

    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::SERVICE_DEFINITION_START_BUTTON_PROVIDER)) {
            $definition = $container->getDefinition(self::SERVICE_DEFINITION_START_BUTTON_PROVIDER);
            $definition->setClass(StartTransitionButtonProviderExtension::class);
        }

        if ($container->hasDefinition(self::SERVICE_DEFINITION_BUTTON_PROVIDER)) {
            $definition = $container->getDefinition(self::SERVICE_DEFINITION_BUTTON_PROVIDER);
            $definition->setClass(TransitionButtonProviderExtension::class);
        }
    }
}
