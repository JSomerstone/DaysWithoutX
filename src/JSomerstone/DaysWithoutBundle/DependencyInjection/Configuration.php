<?php

namespace JSomerstone\DaysWithoutBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('j_somerstone_days_without');
        $rootNode->children()
            ->arrayNode('storage')
                ->children()
                    ->scalarNode('counter_path')
                        ->info('Absolute path to store Counters to')
                        ->cannotBeEmpty()
                        ->end()
                    ->scalarNode('user_path')
                        ->info('Absolute path to store Users to')
                        ->cannotBeEmpty()
                        ->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
