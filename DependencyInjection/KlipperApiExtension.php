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

use Klipper\Bundle\MetadataBundle\KlipperMetadataBundle;
use Klipper\Component\Content\Uploader\Uploader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperApiExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('expression.xml');
        $loader->load('request_matcher.xml');
        $loader->load('handler.xml');
        $loader->load('versioning.xml');
        $loader->load('body_listener.xml');
        $loader->load('serializer.xml');
        $loader->load('format.xml');
        $loader->load('resource_listener.xml');
        $loader->load('controller.xml');
        $loader->load('routing_api_prefix.xml');

        if (class_exists(KlipperMetadataBundle::class)) {
            $loader->load('view_transformer.xml');
            $loader->load('serializer_metadata.xml');
            $loader->load('form_metadata.xml');
            $loader->load('routing_metadata.xml');
        }

        if (class_exists(Uploader::class)) {
            $loader->load('content_uploader_listener.xml');
        }

        $this->loadRoutingApiPrefix($config, $container);
        $this->loadRequestMatcher($config, $container);
        $this->loadVersioning($config, $container);
        $this->loadViewHandler($config, $container);
        $this->loadFormat($config, $container);
    }

    /**
     * Load the config for the api request matcher.
     *
     * @param array            $config    The config
     * @param ContainerBuilder $container The container
     */
    private function loadRoutingApiPrefix(array $config, ContainerBuilder $container): void
    {
        $container->getDefinition('klipper_api.routing.pass_loader.api_prefix')
            ->replaceArgument(0, $container->getParameterBag()->resolveValue($config['base_path']))
            ->replaceArgument(1, $container->getParameterBag()->resolveValue($config['base_host']))
            ->replaceArgument(2, $container->getParameterBag()->resolveValue($config['api_prefix_name_patterns']))
        ;
    }

    /**
     * Load the config for the api request matcher.
     *
     * @param array            $config    The config
     * @param ContainerBuilder $container The container
     */
    private function loadRequestMatcher(array $config, ContainerBuilder $container): void
    {
        $container->getDefinition('klipper_api.request_matcher')
            ->replaceArgument(0, $container->getParameterBag()->resolveValue($config['base_path']))
            ->replaceArgument(1, $container->getParameterBag()->resolveValue($config['base_host']))
        ;
    }

    /**
     * Load the config for listener of version.
     *
     * @param array            $config    The config
     * @param ContainerBuilder $container The container
     */
    private function loadVersioning(array $config, ContainerBuilder $container): void
    {
        $container->getDefinition('klipper_api.versioning.version_listener')
            ->replaceArgument(3, $config['versioning']['default_version'])
        ;

        $container->getDefinition('klipper_api.versioning.available_versions_listener')
            ->replaceArgument(1, $config['versioning']['available_versions'])
        ;

        $container->getDefinition('klipper_api.versioning.version_resolver')
            ->replaceArgument(0, $config['versioning']['resolvers']['custom_header']['header_name'])
        ;
    }

    /**
     * Load the config for view handler and serializer.
     *
     * @param array            $config    The config
     * @param ContainerBuilder $container The container
     */
    private function loadViewHandler(array $config, ContainerBuilder $container): void
    {
        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = \constant('\Symfony\Component\HttpFoundation\Response::'.$config['view']['empty_content']);
        }

        $viewHandlerDef = $container->getDefinition('klipper_api.view_handler');
        $viewHandlerDef
            ->replaceArgument(4, $config['view']['empty_content'])
            ->replaceArgument(5, $config['view']['serialize_null'])
            ->addMethodCall('setSerializeNullStrategy', [$config['serializer']['serialize_null']])
            ->addMethodCall('setMaxDepthChecks', [$config['serializer']['max_depth_checks']])
        ;

        if (!empty($config['serializer']['version'])) {
            $viewHandlerDef->addMethodCall('setExclusionStrategyVersion', [$config['serializer']['version']]);
        }

        if (!empty($config['serializer']['groups'])) {
            $viewHandlerDef->addMethodCall('setExclusionStrategyGroups', [$config['serializer']['groups']]);
        }
    }

    /**
     * Load the config for format listener.
     *
     * @param array            $config    The config
     * @param ContainerBuilder $container The container
     */
    private function loadFormat(array $config, ContainerBuilder $container): void
    {
        $container->getDefinition('klipper_api.listener.format_subscriber')
            ->replaceArgument(5, $config['format']['default_type_mime'])
            ->replaceArgument(6, $config['format']['throw_unsupported_type_mime'])
            ->replaceArgument(7, $config['format']['debug'])
        ;
    }
}
