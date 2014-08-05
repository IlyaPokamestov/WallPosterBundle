<?php

namespace Justy\Bundle\WallPosterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wall_poster');

        $rootNode
            ->children()
                ->arrayNode('vk')
                    ->children()
                        ->scalarNode('access_token')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('group_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('api_version')->defaultValue('5.24')->end()
                        ->scalarNode('lang')->defaultValue('en')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
