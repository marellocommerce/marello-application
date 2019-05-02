<?php

namespace Marello\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    const DEFAULT_LOCALE   = 'en';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('marello_locale');

        // null values set as default for language, country and currency because
        // their values will be calculated by Extension based on chosen locale
        SettingsBuilder::append(
            $rootNode,
            array(
                'languages_email' => [
                    'type' => 'array',
                    'value' => [self::DEFAULT_LOCALE]
                ],
            )
        );

        return $treeBuilder;
    }
}
