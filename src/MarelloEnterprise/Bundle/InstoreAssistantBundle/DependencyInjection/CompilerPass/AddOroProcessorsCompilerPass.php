<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\DependencyInjection\CompilerPass;

use Oro\Bundle\ApiBundle\Util\DependencyInjectionUtil;
use Oro\Component\ChainProcessor\DependencyInjection\ProcessorsLoader;
use Oro\Component\ChainProcessor\ProcessorBagConfigBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class AddOroProcessorsCompilerPass implements CompilerPassInterface
{
    const ORO_PROCESSOR_TAG = 'oro.api.processor';
    const AUTH_ACTION_NAME = 'authenticate';

    const ACTION_PROCESSOR_BAG_SERVICE_ID          = 'oro_api.action_processor_bag';
    const PROCESSOR_BAG_CONFIG_PROVIDER_SERVICE_ID = 'oro_api.processor_bag_config_provider';
    const CUSTOMIZE_LOADED_DATA_ACTION             = 'customize_loaded_data';
    const IDENTIFIER_ONLY_ATTRIBUTE                = 'identifier_only';
    const COLLECTION_ATTRIBUTE                     = 'collection';
    const GROUP_ATTRIBUTE                          = 'group';

    protected $processors = [
        'marelloenterprise_instoreassistant.api.processor.authenticate.processor'
    ];

    protected $actions = [
        'create'
    ];

    /** @var array  */
    protected $groups = [
        'initialize',
        'normalize_input',
        'security_check',
        'load_data',
        'normalize_data',
        'finalize',
        'normalize_result'
    ];

    protected $ignoreProcessorIds = [
        'oro_api.create.json_api.extract_entity_id',
        'oro_api.create.json_api.validate_request_data',
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
        'oro_api.initialize_entity_mapper',
        'oro_api.convert_entity_to_model',
        'oro_api.remove_entity_mapper',
        'oro_pricing.update.load_normalized_entity',
        'oro_product.api.create.related_product.security_check',
    ];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $actionProcessorBagServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            self::ACTION_PROCESSOR_BAG_SERVICE_ID
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
            self::PROCESSOR_BAG_CONFIG_PROVIDER_SERVICE_ID
        );

        if (null !== $processorBagConfigProviderServiceDef) {
            $groups = [];
            foreach ($config['actions'] as $action => $actionConfig) {
                if (isset($actionConfig['processing_groups'])) {
                    foreach ($actionConfig['processing_groups'] as $group => $groupConfig) {
                        $groups[$action][$group] = DependencyInjectionUtil::getPriority($groupConfig);
                    }
                }
            }
            $groups[self::CUSTOMIZE_LOADED_DATA_ACTION] = ['item' => 0, 'collection' => -1];
            $processors = ProcessorsLoader::loadProcessors($container, self::ORO_PROCESSOR_TAG);
            $builder = new ProcessorBagConfigBuilder($groups, $processors);
            $processorBagConfigProviderServiceDef->replaceArgument(0, $builder->getGroups());
            $processorBagConfigProviderServiceDef
                ->replaceArgument(1, $this->normalizeProcessors($builder->getProcessors()));
        }
    }

    /**
     * @param array $allProcessors [action => [[processor id, [attribute name => attribute value, ...]], ...], ...]
     *
     * @return array [action => [[processor id, [attribute name => attribute value, ...]], ...], ...]
     */
    private function normalizeProcessors(array $allProcessors): array
    {
        // normalize "customize_loaded_data" processors
        // and split processors to "item" and "collection" groups
        if (!empty($allProcessors[self::CUSTOMIZE_LOADED_DATA_ACTION])) {
            $itemProcessors = [];
            $collectionProcessors = [];
            $processors = $allProcessors[self::CUSTOMIZE_LOADED_DATA_ACTION];
            foreach ($processors as $key => $item) {
                if (array_key_exists(self::GROUP_ATTRIBUTE, $item[1])) {
                    throw new LogicException(sprintf(
                        'The "%s" processor uses the "%s" tag attribute that is not allowed'
                        . ' for "%s" action. Use "%s" tag attribute instead.',
                        $item[0],
                        self::GROUP_ATTRIBUTE,
                        self::CUSTOMIZE_LOADED_DATA_ACTION,
                        self::COLLECTION_ATTRIBUTE
                    ));
                }
                $isCollectionProcessor = array_key_exists(self::COLLECTION_ATTRIBUTE, $item[1])
                    && $item[1][self::COLLECTION_ATTRIBUTE];
                unset($item[1][self::COLLECTION_ATTRIBUTE]);
                if ($isCollectionProcessor) {
                    $item[1][self::GROUP_ATTRIBUTE] = 'collection';
                    // "identifier_only" attribute is not supported for collections
                    unset($item[1][self::IDENTIFIER_ONLY_ATTRIBUTE]);
                    $collectionProcessors[] = $item;
                } else {
                    $item[1][self::GROUP_ATTRIBUTE] = 'item';
                    // normalize "identifier_only" attribute
                    if (!array_key_exists(self::IDENTIFIER_ONLY_ATTRIBUTE, $item[1])) {
                        // add "identifier_only" attribute to the beginning of an attributes array,
                        // it will give a small performance gain at the runtime
                        $item[1] = [self::IDENTIFIER_ONLY_ATTRIBUTE => false] + $item[1];
                    } elseif (null === $item[1][self::IDENTIFIER_ONLY_ATTRIBUTE]) {
                        unset($item[1][self::IDENTIFIER_ONLY_ATTRIBUTE]);
                    }
                    $itemProcessors[] = $item;
                }
            }
            $allProcessors[self::CUSTOMIZE_LOADED_DATA_ACTION] =
                array_merge($itemProcessors, $collectionProcessors);
        }

        ksort($allProcessors);

        return $allProcessors;
    }
}
