<?php

namespace Zhortein\ElasticEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** @const array|string[] DEFAULT_HOSTS */
    public const array DEFAULT_HOSTS = ['http://localhost:9200'];
    public const int DEFAULT_RETRIES = 0;
    public const string DEFAULT_CA_BUNDLE_PATH = '';
    public const bool DEFAULT_ELASTIC_META_HEADER = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zhortein_elastic_entity');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('hosts')
                    ->scalarPrototype()->end()
                    ->defaultValue(self::DEFAULT_HOSTS)
                ->end()
                ->integerNode('retries')->defaultValue(self::DEFAULT_RETRIES)->end()
                ->scalarNode('ca_bundle_path')->defaultValue(self::DEFAULT_CA_BUNDLE_PATH)->end()
                ->booleanNode('elastic_meta_header')->defaultValue(self::DEFAULT_ELASTIC_META_HEADER)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
