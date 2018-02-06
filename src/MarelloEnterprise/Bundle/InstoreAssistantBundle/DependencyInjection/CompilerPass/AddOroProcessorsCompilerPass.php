<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Component\ChainProcessor\ProcessorBagConfigBuilder;
use Oro\Bundle\ApiBundle\DependencyInjection\OroApiExtension;
use Oro\Component\ChainProcessor\DependencyInjection\ProcessorsLoader;
use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ConfigurationCompilerPass;

class AddOroProcessorsCompilerPass implements CompilerPassInterface
{
    const ORO_PROCESSOR_TAG = 'oro.api.processor';
    const AUTH_ACTION_NAME = 'authenticate';

    protected $processors = [
        'marelloenterprise_instoreassistant.api.processor.authenticate.processor',
        'marelloenterprise_instoreassistant.api.processor.options.processor'
    ];

    protected $actions = [
        'create'
    ];

    /** @var array  */
    protected $groups = [
        'initialize',
        // tmp remove resource_check group during issues with correct action configuration
//        'resource_check',
        'normalize_input',
        'security_check',
        'load_data',
        'normalize_data',
        'finalize',
        'normalize_result'
    ];

    protected $ignoreProcessorIds = [
        'oro_api.create.json_api.extract_entity_id',
        'oro_api.create.rest.normalize_entity_id',
        'oro_api.restore_default_form_extension',
        'oro_api.create.create_entity',
        'oro_api.create.process_localized_values',
        'oro_api.json_api.normalize_included_data',
        'oro_api.add_included_entities_to_result_document',
        'oro_api.set_primary_entity',
        'oro_api.create.set_entity_id',
        'oro_api.create.load_normalized_entity',
        'oro_api.load_normalized_included_entities',
    ];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $actionProcessorBagServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            OroApiExtension::ACTION_PROCESSOR_BAG_SERVICE_ID
        );

        if (null !== $actionProcessorBagServiceDef) {
            // register an action processor in the bag
            foreach ($this->processors as $processorServiceId) {
                $actionProcessorBagServiceDef->addMethodCall(
                    'addProcessor',
                    [new Reference($processorServiceId)]
                );
            }
        }

        // Add all processors in the $groups variable to the authenticate action as new tag
        // in order to not completely diverge from the "oro way" of using the API
        // and keeping the extendability in place for other to use/modify the custom API endpoint
        $taggedServices = $container->findTaggedServiceIds(self::ORO_PROCESSOR_TAG);
        if (empty($taggedServices)) {
            return;
        }

        foreach ($taggedServices as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $isAdded = false;
            foreach ($tags as $tag) {
                if (!in_array($tag['action'], $this->actions) || in_array($serviceId, $this->ignoreProcessorIds)) {
                    continue;
                }

                if (isset($tag['group']) && in_array($tag['group'], $this->groups) && !$isAdded) {
                    $tagParameters = [
                        'action' => self::AUTH_ACTION_NAME,
                        'group' => $tag['group']
                    ];

                    if (array_key_exists('priority', $tag)) {
                        $tagParameters['priority'] = $tag['priority'];
                    }

                    if (array_key_exists('requestType', $tag)) {
                        $tagParameters['requestType'] = $tag['requestType'];
                    }

                    $serviceDefinition->addTag(
                        self::ORO_PROCESSOR_TAG,
                        $tagParameters
                    );
                    $isAdded = true;
                }
            }
        }

        $this->reBuildProcessorBagConfig($container);
    }

    /**
     * Rebuild the ProcessorBagConfig provider with the updated service definitions and processors
     * @param $container
     */
    protected function reBuildProcessorBagConfig($container)
    {
        $config = DependencyInjectionUtil::getConfig($container);
        $processorBagConfigProviderServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            ConfigurationCompilerPass::PROCESSOR_BAG_CONFIG_PROVIDER_SERVICE_ID
        );

        if (null !== $processorBagConfigProviderServiceDef) {
            $groups = [];
            foreach ($config['actions'] as $action => $actionConfig) {
                if (isset($actionConfig['processing_groups'])) {
                    foreach ($actionConfig['processing_groups'] as $group => $groupConfig) {
                        $groups[$action][$group] = isset($groupConfig['priority']) ? $groupConfig['priority'] : 0;
                    }
                }
            }

            $processors = ProcessorsLoader::loadProcessors($container, self::ORO_PROCESSOR_TAG);
            $builder = new ProcessorBagConfigBuilder($groups, $processors);
            $processorBagConfigProviderServiceDef->replaceArgument(0, $builder->getGroups());
            $processorBagConfigProviderServiceDef->replaceArgument(1, $builder->getProcessors());
        }
    }
}
