<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle;

use Klipper\Bundle\ApiBundle\DependencyInjection\Compiler\ControllerViewTransformerPass;
use Klipper\Bundle\ApiBundle\DependencyInjection\Compiler\ViewTransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class KlipperApiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ViewTransformerPass());
        $container->addCompilerPass(new ControllerViewTransformerPass());
    }
}
