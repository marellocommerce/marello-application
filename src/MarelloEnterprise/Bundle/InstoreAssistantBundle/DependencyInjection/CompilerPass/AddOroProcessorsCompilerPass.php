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
    const CUSTOM_ACTION_NAME = 'authenticate';
    const CUSTOM_ACTION_PROCESSOR = 'marelloenterprise_instoreassistant.api.processor.authenticate.processor';

    /** @var array  */
    protected $groups = [
        'initialize',
        'resource_check',
        'normalize_input',
        'security_check',
        'load_data',
        'transform_data',
        'normalize_data',
        'finalize',
        'normalize_result'
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
            $actionProcessorBagServiceDef->addMethodCall(
                'addProcessor',
                [new Reference(self::CUSTOM_ACTION_PROCESSOR)]
            );
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
            if (!$this->searchTags(self::CUSTOM_ACTION_NAME, $tags)) {
                continue;
            }

            $isAdded = false;
            foreach ($tags as $tag) {
                if ($tag['action'] === self::CUSTOM_ACTION_NAME) {
                    continue;
                }

                if (isset($tag['group']) && in_array($tag['group'], $this->groups) && !$isAdded) {
                    $serviceDefinition->addTag(
                        self::ORO_PROCESSOR_TAG,
                        [
                            'action' => self::CUSTOM_ACTION_NAME,
                            'group' => $tag['group'],
                            'priority' => array_key_exists('priority', $tag) ? $tag['priority'] : null,
                            'requestType' => array_key_exists('requestType', $tag) ? $tag['requestType'] : null
                        ]
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

    /**
     * Search tags in the array with arguments recursively
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @param array $path
     * @return array|bool
     */
    protected function searchTags($needle, $haystack, $strict = false, $path = [])
    {
        if (!is_array($haystack)) {
            return false;
        }

        foreach ($haystack as $key => $val) {
            if (is_array($val) && $subPath = $this->searchTags($needle, $val, $strict, $path)) {
                $path = array_merge($path, [$key], $subPath);
                return $path;
            } elseif ((!$strict && $val == $needle) || ($strict && $val === $needle)) {
                $path[] = $key;
                return $path;
            }
        }
        return false;
    }
}
