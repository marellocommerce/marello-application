<?php

namespace Marello\Bundle\POSUserBundle\DependencyInjection\CompilerPass;

use Marello\Bundle\POSUserBundle\Api\Processor\AuthenticateRequestActionProcessor;
use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddAuthenticateProcessorCompilerPass implements CompilerPassInterface
{
    private const ACTION_PROCESSOR_BAG_SERVICE_ID = 'oro_api.action_processor_bag';

    public function process(ContainerBuilder $container)
    {
        $actionProcessorBagServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            self::ACTION_PROCESSOR_BAG_SERVICE_ID
        );

        $actionProcessorBagServiceDef?->addMethodCall(
            'addProcessor',
            [new Reference(AuthenticateRequestActionProcessor::class)]
        );
    }
}
