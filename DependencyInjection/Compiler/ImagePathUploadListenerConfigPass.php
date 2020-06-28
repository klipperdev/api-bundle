<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ImagePathUploadListenerConfigPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('klipper_api.listener.image_path_upload_subscriber')) {
            return;
        }

        $def = $container->getDefinition('klipper_api.listener.image_path_upload_subscriber');

        foreach ($this->findAndSortTaggedServices('klipper_api.listener.image_path_upload_config', $container) as $service) {
            $def->addMethodCall('addImagePathUploadListenerConfig', [$service]);
        }
    }
}
