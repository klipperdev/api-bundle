<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is the class that validates and merges configuration from your config files.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Configuration implements ConfigurationInterface
{
    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('klipper_api');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('base_host')->defaultNull()->end()
            ->scalarNode('base_path')->defaultNull()->end()
            ->arrayNode('api_prefix_name_patterns')
            ->scalarPrototype()->end()
            ->defaultValue(['*_api*'])
            ->end()
            ->end()
        ;

        $this->addVersioningSection($rootNode);
        $this->addViewSection($rootNode);
        $this->addSerializerSection($rootNode);
        $this->addFormatSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add versioning section.
     */
    private function addVersioningSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('versioning')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('default_version')->defaultNull()->end()
            ->arrayNode('available_versions')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('resolvers')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('custom_header')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('header_name')->defaultValue('X-Accept-Version')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;
    }

    /**
     * Add view handler section.
     */
    private function addViewSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('view')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('empty_content')->defaultValue(Response::HTTP_NO_CONTENT)->end()
            ->booleanNode('serialize_null')->defaultFalse()->end()
            ->end()
            ->end()
            ->end()
        ;
    }

    /**
     * Add serializer section.
     */
    private function addSerializerSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('serializer')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('serialize_null')->defaultFalse()->end()
            ->booleanNode('max_depth_checks')->defaultTrue()->end()
            ->scalarNode('version')->defaultNull()->end()
            ->arrayNode('groups')
            ->prototype('scalar')->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;
    }

    /**
     * Add format section.
     */
    private function addFormatSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('format')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('default_type_mime')->defaultValue('application/json')->end()
            ->scalarNode('throw_unsupported_type_mime')->defaultTrue()->end()
            ->scalarNode('debug')->defaultValue($this->debug)->end()
            ->end()
            ->end()
            ->end()
        ;
    }
}
