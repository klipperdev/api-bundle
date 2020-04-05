<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Bundle\ApiBundle\Version\Resolver;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface VersionResolverInterface
{
    /**
     * Resolves the version of a request.
     *
     * @return false|float|int|string The current version or false if not resolved
     */
    public function resolve(Request $request);
}
