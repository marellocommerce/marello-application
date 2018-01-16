<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class AddOroProcessorsCompilerPass implements CompilerPassInterface
{
    const TAG = 'oro.api.processor';
    const TAG_ACTION_FILTER = 'create';
    const TAG_GROUP_FILTERS = ['load_data','save_data', 'transform_data'];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Add all processors related to the 'create' action to the authenticate action
        // in order to not completely diverge from the "oro way" of using the API
        // and keeping the extendability in place for other to use/modify the custom API endpoint
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        if (empty($taggedServices)) {
            return;
        }

        foreach ($taggedServices as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            foreach ($tags as $tag) {
                // filter out tags which are not part of the 'create' action
                // filter out tags which have the groups save_data and transform_data
                if (!isset($tag['action']) || $tag['action'] !== self::TAG_ACTION_FILTER
                || (isset($tag['group']) && in_array($tag['group'], self::TAG_GROUP_FILTERS))) {
                    continue;
                }

                $serviceDefinition->addTag(
                    self::TAG,
                    [
                        'action' => 'authenticate',
                        'group' => array_key_exists('group', $tag) ? $tag['group'] : null,
                        'priority' => array_key_exists('priority', $tag) ? $tag['priority'] : null,
                        'requestType' => array_key_exists('requestType', $tag) ? $tag['requestType'] : null
                    ]
                );
            }
        }
    }
}
