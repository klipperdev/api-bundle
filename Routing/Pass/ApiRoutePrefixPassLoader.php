<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Routing\Pass;

use Klipper\Component\Routing\Loader\PassLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ApiRoutePrefixPassLoader implements PassLoaderInterface
{
    private ?string $baseHost;

    private ?string $basePath;

    /**
     * @var string[]
     */
    private array $patterns;

    /**
     * @param string[] $patterns
     */
    public function __construct(?string $basePath, ?string $baseHost, array $patterns = [])
    {
        $this->baseHost = $baseHost;
        $this->basePath = $basePath;
        $this->patterns = $patterns;
    }

    public function load(RouteCollection $collection): RouteCollection
    {
        if (empty($this->patterns) || (null === $this->baseHost && null === $this->basePath)) {
            return $collection;
        }

        foreach ($collection->all() as $name => $route) {
            if ($this->isValid($name)) {
                if (null !== $this->baseHost && empty($route->getHost())) {
                    $route->setHost($this->baseHost);
                }

                if (null !== $this->basePath && 0 !== strpos($route->getPath(), $this->basePath)) {
                    $route->setPath($this->basePath.$route->getPath());
                }
            }
        }

        return $collection;
    }

    private function isValid(string $name): bool
    {
        foreach ($this->patterns as $pattern) {
            if (fnmatch($pattern, $name)) {
                return true;
            }
        }

        return false;
    }
}
