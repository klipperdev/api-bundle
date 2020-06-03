<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Routing\Loader;

use Klipper\Component\Metadata\MetadataManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MetadataLoader extends Loader
{
    private MetadataManagerInterface $manager;

    private string $prefix;

    /**
     * @param MetadataManagerInterface $manager The metadata manager
     * @param string                   $prefix  The prefix of route name
     */
    public function __construct(MetadataManagerInterface $manager, string $prefix = 'app_api_')
    {
        $this->manager = $manager;
        $this->prefix = $prefix;
    }

    public function load($resource, string $type = null): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->manager->all() as $metadata) {
            if ($metadata->isPublic()) {
                foreach ($metadata->getActions() as $action) {
                    $name = $this->prefix.$metadata->getPluralName().'_'.$action->getName();

                    $routes->add($name, new Route(
                        (string) $action->getPath(),
                        $action->getDefaults(),
                        $action->getRequirements(),
                        $action->getOptions(),
                        $action->getHost(),
                        $action->getSchemes(),
                        $action->getMethods(),
                        $action->getCondition()
                    ));
                }

                foreach ($metadata->getResources() as $metaResource) {
                    $routes->addResource($metaResource);
                }
            }
        }

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'metadata' === $type;
    }
}
