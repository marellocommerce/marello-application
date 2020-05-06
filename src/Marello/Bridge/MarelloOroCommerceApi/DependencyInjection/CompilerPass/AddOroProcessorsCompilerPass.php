<?php

namespace Marello\Bridge\MarelloOroCommerceApi\DependencyInjection\CompilerPass;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Component\ChainProcessor\DependencyInjection\ProcessorsLoader;
use Oro\Component\ChainProcessor\ProcessorBagConfigBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class AddOroProcessorsCompilerPass implements CompilerPassInterface
{
    const ACTION_PROCESSOR_BAG_SERVICE_ID  = 'oro_api.action_processor_bag';
    const CREATE_COLLECTION_ACTION_SERVICE_ID = 'marello_commerce_bridge.create_collection.processor';
    const UPDATE_COLLECTION_ACTION_SERVICE_ID = 'marello_commerce_bridge.update_collection.processor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $actionProcessorBagServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            self::ACTION_PROCESSOR_BAG_SERVICE_ID
        );

        $actionProcessorBagServiceDef->addMethodCall(
            'addProcessor',
            [new Reference(self::CREATE_COLLECTION_ACTION_SERVICE_ID)]
        );
        $actionProcessorBagServiceDef->addMethodCall(
            'addProcessor',
            [new Reference(self::UPDATE_COLLECTION_ACTION_SERVICE_ID)]
        );
    }
}
