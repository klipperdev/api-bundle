<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Util;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ControllerDefinitionUtil
{
    /**
     * Register the controller as a service and add the required tags.
     */
    public static function set(
        ContainerBuilder $container,
        string $controllerClass,
        array $arguments = []
    ): Definition {
        return $container->setDefinition(
            $controllerClass,
            (new Definition($controllerClass, $arguments))
                ->setPublic(true)
                ->addTag('controller.service_arguments')
        );
    }
}
