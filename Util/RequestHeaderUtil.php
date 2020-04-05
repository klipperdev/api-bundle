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

use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class RequestHeaderUtil
{
    /**
     * Get the boolean value of request header.
     *
     * @param Request     $request The request
     * @param string      $key     The header key
     * @param null|string $default The default value
     */
    public static function getBoolean(Request $request, string $key, ?string $default = null): bool
    {
        return filter_var($request->headers->get(strtolower($key), $default), FILTER_VALIDATE_BOOLEAN);
    }
}
