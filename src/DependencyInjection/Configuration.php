<?php declare(strict_types=1);

namespace Somnambulist\Bundles\ReadModelsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('somnambulist_read_models');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('model')->scalarPrototype()->end()
                ->end()
                ->arrayNode('subscribers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('request_manager_clearer')->defaultTrue()->end()
                        ->booleanNode('messenger_manager_clearer')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
